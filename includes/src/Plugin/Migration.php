<?php

declare(strict_types=1);

namespace JTL\Plugin;

/**
 * Class Migration
 * @package JTL\Plugin
 */
class Migration extends \JTL\Update\Migration
{
    public function getId(): ?int
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }
}
