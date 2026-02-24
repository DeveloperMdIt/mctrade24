<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

/**
 * Class Hook
 * @package JTL\Plugin\Data
 */
class Hook
{
    private string $file;

    private int $id;

    private int $pluginID;

    private int $priority;

    private int $calledHookID = -1;

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function getID(): int
    {
        return $this->id;
    }

    public function setID(int $id): void
    {
        $this->id = $id;
    }

    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getCalledHookID(): int
    {
        return $this->calledHookID;
    }

    public function setCalledHookID(int $calledHookID): void
    {
        $this->calledHookID = $calledHookID;
    }
}
