<?php

declare(strict_types=1);

namespace JTL\Template\Admin;

use DirectoryIterator;
use Exception;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTL\Template\Admin\Validation\ValidatorInterface;
use JTL\Template\Model;
use JTL\XMLParser;

/**
 * Class Listing
 * @package JTL\Template\Admin
 */
final class Listing
{
    private const TEMPLATE_DIR = \PFAD_ROOT . \PFAD_TEMPLATES;

    /**
     * @var Collection<ListingItem>
     */
    private Collection $items;

    public function __construct(private readonly DbInterface $db, private readonly ValidatorInterface $validator)
    {
        $this->items = new Collection();
    }

    /**
     * @return Collection<ListingItem>
     * @former gibAllePlugins()
     */
    public function getAll(): Collection
    {
        $parser = new XMLParser();
        $this->parseTemplateDir($parser);
        $this->sort();

        return $this->items;
    }

    /**
     * @throws Exception
     */
    private function getActiveTemplate(): Model
    {
        return Model::loadByAttributes(['type' => 'standard'], $this->db);
    }

    /**
     * @throws Exception
     */
    private function getPreviewTemplate(): Model
    {
        return Model::loadByAttributes(['type' => 'test'], $this->db);
    }

    /**
     * @return Collection<ListingItem>
     */
    private function parseTemplateDir(XMLParser $parser): Collection
    {
        if (!\is_dir(self::TEMPLATE_DIR)) {
            return $this->items;
        }
        try {
            $active = $this->getActiveTemplate();
        } catch (Exception) {
            $active = new Model($this->db);
            $active->setTemplate('no-template');
        }
        try {
            $preview = $this->getPreviewTemplate()->getTemplate();
        } catch (Exception) {
            $preview = null;
        }
        $gettext = Shop::Container()->getGetText();
        foreach (new DirectoryIterator(self::TEMPLATE_DIR) as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $dir  = $fileinfo->getBasename();
            $info = $fileinfo->getPathname() . '/' . \TEMPLATE_XML;
            if (!\file_exists($info)) {
                continue;
            }
            $xml                 = $parser->parse($info);
            $code                = $this->validator->validate(self::TEMPLATE_DIR . $dir, $xml);
            $xml['cVerzeichnis'] = $dir;
            $xml['cFehlercode']  = $code;
            $item                = new ListingItem();
            $item->parseXML($xml, $code);
            $item->setPath(self::TEMPLATE_DIR . $dir);
            $item->setActive($item->getDir() === $active->getTemplate());
            $item->setIsPreview($item->getDir() === $preview);

            $gettext->loadTemplateItemLocale('base', $item);
            $msgid = $item->getFramework() . '_desc';
            $desc  = \__($msgid);
            $item->setDescription($desc !== $msgid ? $desc : \__($item->getDescription()));
            $item->setAuthor(\__($item->getAuthor()));
            $item->setName(\__($item->getName()));
            if ($code === TemplateValidator::RES_OK) {
                $item->setAvailable(true);
                $item->setHasError(false);
            } else {
                $item->setAvailable(false);
                $item->setHasError(true);
                $item->setErrorCode($code);
            }
            $this->items[] = $item;
        }

        return $this->items;
    }

    private function sort(): void
    {
        $this->items = $this->items->sortBy(static function (ListingItem $item): string {
            return \mb_convert_case($item->getName(), \MB_CASE_LOWER);
        });
    }
}
