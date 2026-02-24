<?php

declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Link\LinkGroupInterface;
use JTL\Link\LinkInterface;
use JTL\Shop;

/**
 * Class LinkGroup
 * @package JTL\Boxes\Items
 */
final class LinkGroup extends AbstractBox
{
    private ?LinkGroupInterface $linkGroup = null;

    public ?string $linkGroupTemplate = null;

    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('oLinkGruppe', 'LinkGroup');
        $this->addMapping('oLinkGruppeTemplate', 'LinkGroupTemplate');
    }

    /**
     * @inheritdoc
     */
    public function map(array $boxData): void
    {
        parent::map($boxData);
        $this->setShow(false);
        $this->linkGroup = Shop::Container()->getLinkService()->getLinkGroupByID($this->getCustomID());
        if ($this->linkGroup !== null) {
            $this->linkGroup->setLinks(
                $this->linkGroup->getLinks()->filter(fn(LinkInterface $link) => $link->getPluginEnabled())
            );
            $this->setShow($this->linkGroup->getLinks()->count() > 0);
            $this->setLinkGroupTemplate($this->linkGroup->getTemplate());
        }
    }

    public function getLinkGroup(): ?LinkGroupInterface
    {
        return $this->linkGroup;
    }

    public function setLinkGroup(?LinkGroupInterface $linkGroup): void
    {
        $this->linkGroup = $linkGroup;
    }

    public function getLinkGroupTemplate(): string
    {
        return $this->linkGroupTemplate;
    }

    public function setLinkGroupTemplate(string $linkGroupTemplate): void
    {
        $this->linkGroupTemplate = $linkGroupTemplate;
    }
}
