<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

use JTL\License\Struct\ExsLicense;

/**
 * Class License
 * @package JTL\Plugin\Data
 */
class License
{
    private ?string $key = null;

    /**
     * @var class-string|null
     */
    private ?string $className = null;

    /**
     * @var class-string|null
     */
    private ?string $class = null;

    private ?ExsLicense $exsLicense = null;

    public function hasLicenseCheck(): bool
    {
        return !empty($this->class) && !empty($this->className);
    }

    public function hasLicense(): bool
    {
        return $this->hasLicenseCheck() && !empty($this->key);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param class-string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param class-string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getExsLicense(): ?ExsLicense
    {
        return $this->exsLicense;
    }

    public function setExsLicense(?ExsLicense $exsLicense): void
    {
        $this->exsLicense = $exsLicense;
    }
}
