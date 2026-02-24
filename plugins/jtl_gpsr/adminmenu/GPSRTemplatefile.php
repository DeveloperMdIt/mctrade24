<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr\adminmenu;

use Exception;
use FilesystemIterator;
use Illuminate\Support\Collection;
use JTL\Plugin\Data\Config as PluginConfig;
use JTL\Shop;
use JTL\Template\Admin\Listing;
use JTL\Template\Admin\ListingItem;
use JTL\Template\Admin\Validation\TemplateValidator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use stdClass;

/**
 * Class GPSRTemplatefile
 * @package Plugin\jtl_gpsr\adminmenu
 */
class GPSRTemplatefile
{
    public const DETAIL = 'productdetails';

    public const LIST = 'productlist';

    protected static array $instances = [];

    /** @var string[]|null  */
    protected ?array $content = null;

    protected string $context;

    protected PluginConfig $config;

    protected ?Collection $allTemplates = null;

    /**
     * GPSRTemplatefile constructor
     * @param string       $context
     * @param PluginConfig $config
     */
    protected function __construct(string $context, PluginConfig $config)
    {
        $this->context = $context;
        $this->config  = $config;
    }

    public static function getInstance(string $context, PluginConfig $config)
    {
        $realContext = static::getRealContext($context);
        if (!isset(self::$instances[$realContext])) {
            self::$instances[$realContext] = new static($context, $config);
        }

        return self::$instances[$realContext];
    }

    protected static function getRealContext(string $context): string
    {
        return 'file:' . $context;
    }

    /**
     * @param string $tplDir
     * @param string $context
     * @return string[]
     */
    protected function getTemplateList(string $tplDir, string $context): array
    {
        $tplList = [];
        $path    = $tplDir . $context;
        try {
            $rdi = new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
            );
        } catch (Exception $e) {
            return [];
        }

        foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::LEAVES_ONLY) as $fileinfo) {
            if (!$fileinfo->isFile() || $fileinfo->getExtension() !== 'tpl') {
                continue;
            }

            $tplList[] = \str_replace($tplDir, '', $fileinfo->getPathname());
        }
        \sort($tplList);

        return $tplList;
    }

    protected function getTemplateByName(string $tplName): ?ListingItem
    {
        $db = Shop::Container()->getDB();
        if ($this->allTemplates === null) {
            $this->allTemplates = (new Listing($db, new TemplateValidator($db)))->getAll();
        }

        return $this->allTemplates->first(function (ListingItem $item) use ($tplName) {
            return $item->getName() === $tplName;
        });
    }

    /**
     * @return stdClass[]
     */
    protected function getEmpyOption(): array
    {
        $option = (object)[
            'cWert' => '$path',
            'cName' => __('Templateverzeichnis existiert nicht'),
            'nSort' => 1,
        ];

        return [$option];
    }

    /**
     * @param string $context
     * @return stdClass[]
     */
    protected function getContextList(string $context): array
    {
        $nSort       = 1;
        $contextList = [];
        try {
            $activeTpl = Shop::Container()->getTemplateService()->getActiveTemplate(false);
            $curTpl    = $this->getTemplateByName($activeTpl->getName());
        } catch (Exception $e) {
            $curTpl = null;
        }

        if ($curTpl === null) {
            return $this->getEmpyOption();
        }

        $tplList = [];
        do {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $tplList = \array_merge($tplList, $this->getTemplateList($curTpl->getPath() . '/', $context));
            $curTpl  = $this->getTemplateByName($curTpl->getParent() ?? '');
        } while ($curTpl !== null);

        foreach ($tplList as $path) {
            if (
                \array_filter(
                    $contextList,
                    static fn ($item) => ($item->cWert ?? '') === $path,
                )
            ) {
                continue;
            }
            $option = new stdClass();

            $option->cWert = $path;
            $option->cName = $path;
            $option->nSort = $nSort;
            $contextList[] = $option;
            $nSort++;
        }

        return empty($contextList) ? $this->getEmpyOption() : $contextList;
    }

    /**
     * @return stdClass[]
     */
    public function getList(bool $forceReBuild = false): array
    {
        if ($forceReBuild || $this->content === null) {
            $this->content = $this->getContextList($this->context);
        }

        return $this->content;
    }
}
