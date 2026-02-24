<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

use Illuminate\Support\Collection;
use JTL\Plugin\Admin\InputType;
use stdClass;

use function Functional\first;
use function Functional\group;

/**
 * Class Config
 * @package JTL\Plugin\Data
 */
class Config
{
    public const TYPE_NOT_CONFIGURABLE = 'N';

    public const TYPE_CONFIGURABLE = 'Y';

    public const TYPE_DYNAMIC = 'M';

    /**
     * @var Collection<stdClass>
     */
    private Collection $options;

    public function __construct(private string $adminPath)
    {
        $this->options = new Collection();
    }

    /**
     * @param stdClass[] $data
     * @return Config
     */
    public function load(array $data): self
    {
        $grouped = group($data, static function (stdClass $e) {
            return $e->id;
        });
        foreach ($grouped as $values) {
            /** @var stdClass $base */
            $base            = first($values);
            $cfg             = new stdClass();
            $cfg->id         = (int)$base->id;
            $cfg->valueID    = $base->confName;
            $cfg->menuID     = (int)$base->menuID;
            $cfg->name       = $base->confNicename;
            $cfg->inputType  = $base->inputType;
            $cfg->sort       = (int)$base->nSort;
            $cfg->confType   = $base->confType;
            $cfg->sourceFile = $base->sourceFile;
            $cfg->options    = [];
            $cfg->value      = $base->confType === self::TYPE_DYNAMIC
                ? \unserialize($base->currentValue, ['allowed_classes' => false])
                : $base->currentValue;

            $cfg->niceName    = $base->name;
            $cfg->description = $base->description;
            $this->enhance($cfg, $base, $values);
            $this->options->push($cfg);
        }

        return $this;
    }

    public function enhance(stdClass $cfg, stdClass $base, mixed $values): void
    {
        if (
            !empty($cfg->sourceFile)
            && ($cfg->inputType === InputType::SELECT || $cfg->inputType === InputType::RADIO)
        ) {
            $cfg->options = $this->getDynamicOptions($cfg);
        } elseif (!($base->confValue === null && $base->confNicename === null)) {
            foreach ($values as $value) {
                $opt           = new stdClass();
                $opt->value    = $value->confValue;
                $opt->sort     = (int)$value->confSort;
                $opt->niceName = $value->confNicename;

                $cfg->options[] = $opt;
            }
        } elseif ($cfg->inputType === InputType::NUMBER) {
            $cfg->value = (int)$cfg->value;
        }
    }

    /**
     * @param stdClass $conf
     * @return null|array
     */
    public function getDynamicOptions(stdClass $conf): ?array
    {
        $dynamicOptions = null;
        if (!empty($conf->sourceFile) && \file_exists($this->adminPath . $conf->sourceFile)) {
            /** @var stdClass[] $dynamicOptions */
            $dynamicOptions = include $this->adminPath . $conf->sourceFile;
            foreach ($dynamicOptions as $option) {
                $option->kPluginEinstellungenConf = $conf->id;
                $option->id                       = $conf->id;
                $option->niceName                 = $option->cName;
                $option->value                    = $option->cWert;
                if (!isset($option->nSort)) {
                    $option->nSort = 0;
                }
                if (!isset($option->sort)) {
                    $option->sort = $option->nSort;
                }
            }
        }

        return $dynamicOptions;
    }

    public function getOption(string $name): ?stdClass
    {
        return $this->options->first(static function (stdClass $item) use ($name): bool {
            return $item->valueID === $name;
        });
    }

    public function getValue(string $name): mixed
    {
        $item = $this->options->first(static function (stdClass $item) use ($name): bool {
            return $item->valueID === $name;
        });

        return $item->value ?? null;
    }

    public function getAdminPath(): string
    {
        return $this->adminPath;
    }

    public function setAdminPath(string $adminPath): void
    {
        $this->adminPath = $adminPath;
    }

    /**
     * @return Collection<stdClass>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * @param Collection<stdClass> $options
     */
    public function setOptions(Collection $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array<string, stdClass>
     */
    public function getAssoc(): array
    {
        return $this->options->keyBy('valueID')->all();
    }
}
