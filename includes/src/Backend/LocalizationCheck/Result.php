<?php

declare(strict_types=1);

namespace JTL\Backend\LocalizationCheck;

use Illuminate\Support\Collection;

/**
 * Class Result
 * @package JTL\Backend\LocalizationCheck
 */
class Result
{
    private string $className;

    private string $location;

    /**
     * @var Collection<Item>
     */
    private Collection $excessLocalizations;

    /**
     * @var Collection<Item>
     */
    private Collection $missingLocalizations;

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return Collection<Item>
     */
    public function getExcessLocalizations(): Collection
    {
        return $this->excessLocalizations;
    }

    /**
     * @param Collection<Item> $excessLocalizations
     */
    public function setExcessLocalizations(Collection $excessLocalizations): void
    {
        $this->excessLocalizations = $excessLocalizations;
    }

    /**
     * @return Collection<Item>
     */
    public function getMissingLocalizations(): Collection
    {
        return $this->missingLocalizations;
    }

    /**
     * @param Collection<Item> $missingLocalizations
     */
    public function setMissingLocalizations(Collection $missingLocalizations): void
    {
        $this->missingLocalizations = $missingLocalizations;
    }

    public function getErrorCount(): int
    {
        return $this->missingLocalizations->count() + $this->excessLocalizations->count();
    }

    public function hasPassed(): bool
    {
        return $this->getErrorCount() === 0;
    }
}
