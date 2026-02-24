<?php

declare(strict_types=1);

namespace JTL\Plugin\Admin;

use DateTime;
use InvalidArgumentException;
use JsonSerializable;
use JTL\Helpers\GeneralObject;
use JTL\Mapper\PluginValidation;
use JTL\Plugin\InstallCode;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\State;
use JTLShop\SemVer\Version;

/**
 * Class ListingItem
 * @package JTL\Plugin\Admin
 */
class ListingItem implements JsonSerializable
{
    private bool $isShop4Compatible = false;

    private bool $isShop5Compatible = false;

    private string $path = '';

    private string $dir = '';

    private string $name = '';

    private ?Version $version = null;

    private ?Version $minShopVersion = null;

    private ?Version $maxShopVersion = null;

    private string $description = '';

    private string $author = '';

    private ?string $icon = '';

    private int $id = 0;

    private string $pluginID = '';

    private ?string $exsID = null;

    private int $errorCode = 0;

    private string $errorMessage = '';

    private bool $hasError = false;

    private bool $available = false;

    private bool $installed = false;

    private int $state = State::NONE;

    private bool $isLegacy = true;

    private Version|false $updateAvailable = false;

    private bool $hasLicenseCheck = false;

    private string $license = '';

    private ?string $updateFromDir = null;

    private ?DateTime $dateInstalled = null;

    private int $langVarCount = 0;

    private int $linkCount = 0;

    private int $optionsCount = 0;

    private ?string $readmeMD = null;

    private ?string $licenseMD = null;

    private ?PluginInterface $plugin = null;

    /**
     * @param array $xml
     * @return ListingItem
     */
    public function parseXML(array $xml): self
    {
        $node                 = null;
        $this->name           = $xml['cVerzeichnis'];
        $this->dir            = $xml['cVerzeichnis'];
        $this->minShopVersion = Version::parse('5.0.0');
        $this->maxShopVersion = Version::parse('0.0.0');
        if (GeneralObject::isCountable('jtlshopplugin', $xml)) {
            $node                    = $xml['jtlshopplugin'][0];
            $this->isShop5Compatible = true;
        } elseif (GeneralObject::isCountable('jtlshop3plugin', $xml)) {
            $node = $xml['jtlshop3plugin'][0];
        }
        if ($node !== null) {
            if ($this->isShop5Compatible) {
                if (!isset($node['Version'])) {
                    return $this->fail();
                }
            } elseif (!isset($node['Install'][0]['Version'])) {
                return $this->fail();
            }
            if (!isset($node['Name'])) {
                return $this->fail();
            }
            $this->name        = $node['Name'];
            $this->description = $node['Description'] ?? '';
            $this->author      = $node['Author'] ?? '';
            $this->pluginID    = $node['PluginID'] ?? '';
            $this->icon        = $node['Icon'] ?? null;
            $this->exsID       = $node['ExsID'] ?? null;
            if (isset($node['Install'][0]['Version']) && \is_array($node['Install'][0]['Version'])) {
                $lastVersion = \count($node['Install'][0]['Version']) / 2 - 1;
                $version     = (int)($node['Install'][0]['Version'][$lastVersion . ' attr']['nr'] ?? 0);
            } else {
                $version = $node['Version'];
            }
            try {
                $this->version        = Version::parse($version);
                $this->minShopVersion = Version::parse($node['MinShopVersion'] ?? $node['ShopVersion'] ?? '5.0.0');
                $this->maxShopVersion = Version::parse($node['MaxShopVersion'] ?? '0.0.0');
            } catch (InvalidArgumentException) {
            }
        }
        if ($xml['cFehlercode'] !== InstallCode::OK) {
            $mapper             = new PluginValidation();
            $this->hasError     = true;
            $this->errorCode    = $xml['cFehlercode'];
            $this->errorMessage = $mapper->map($xml['cFehlercode'], $this->getPluginID());

            return $this->fail();
        }

        return $this;
    }

    private function fail(): self
    {
        $this->version = $this->version ?? Version::parse('0.0.0');

        return $this;
    }

    public function loadFromPlugin(PluginInterface $plugin): self
    {
        $meta = $plugin->getMeta();
        $this->setName($meta->getName());
        $this->setDescription($meta->getDescription());
        $this->setAuthor($meta->getAuthor());
        $this->setID($plugin->getID());
        $this->setPluginID($plugin->getPluginID());
        $this->setPath($plugin->getPaths()->getBasePath());
        $this->setDir($plugin->getPaths()->getBaseDir());
        $this->setIsLegacy($plugin->isLegacy());
        $this->setIcon($meta->getIcon() ?? '');
        $this->setVersion($meta->getSemVer());
        $this->setState($plugin->getState());
        $this->setDateInstalled($meta->getDateInstalled());
        $this->setLangVarCount($plugin->getLocalization()->getLangVars()->count());
        $this->setLinkCount($plugin->getLinks()->getLinks()->count());
        $this->setHasLicenseCheck($plugin->getLicense()->hasLicenseCheck());
        $this->setOptionsCount(
            $plugin->getConfig()->getOptions()->count()
            + $plugin->getAdminMenu()->getItems()->count()
        );
        $this->setReadmeMD($meta->getReadmeMD());
        $this->setLicenseMD($meta->getLicenseMD());
        $this->setIsShop5Compatible(!$this->isLegacy());
        $this->setLicenseKey($plugin->getLicense()->getKey());
        $this->setUpdateAvailable($plugin->getMeta()->getUpdateAvailable());
        $this->setMinShopVersion(Version::parse('0.0.0'));
        $this->setMaxShopVersion(Version::parse('0.0.0'));
        $license = $plugin->getLicense()->getExsLicense();
        if ($license !== null) {
            $this->setExsID($license->getExsID());
        }
        $this->setPlugin($plugin);

        return $this;
    }

    public function mergeWith(ListingItem $item): void
    {
        $this->setOptionsCount($item->getOptionsCount());
        $this->setDateInstalled($item->getDateInstalled());
        $this->setID($item->getID());
        $this->setState($item->getState());
        $this->setIsShop5Compatible($item->isShop5Compatible());
        $this->setIsShop4Compatible($item->isShop4Compatible());
        $this->setLangVarCount($item->getLangVarCount());
        $this->setReadmeMD($item->getReadmeMD());
        $this->setLicenseMD($item->getLicenseMD());
        $this->setLinkCount($item->getLinkCount());
        $this->setLicenseKey($item->getLicenseKey());
        $this->setHasLicenseCheck($item->hasLicenseCheck());
        $this->setPlugin($item->getPlugin());
    }

    /**
     * @return string[]
     */
    public function getModifiedFiles(): array
    {
        if (!$this->supportsHashCheck()) {
            return [];
        }
        $modified = [];
        $items    = \array_filter(
            \explode(
                \PHP_EOL,
                \file_get_contents($this->getPath() . PluginInterface::FILE_HASHES) ?: ''
            )
        );
        foreach ($items as $item) {
            [$file, $hash] = \explode('###', $item);
            if (\md5_file($this->getPath() . $file) !== $hash) {
                $modified[] = $file;
            }
        }

        return $modified;
    }

    public function supportsHashCheck(): bool
    {
        return \file_exists($this->getPath() . PluginInterface::FILE_HASHES);
    }

    public function isShop4Compatible(): bool
    {
        return $this->isShop4Compatible;
    }

    public function setIsShop4Compatible(bool $isShop4Compatible): void
    {
        $this->isShop4Compatible = $isShop4Compatible;
    }

    public function isShop5Compatible(): bool
    {
        return $this->isShop5Compatible;
    }

    public function setIsShop5Compatible(bool $isShop5Compatible): void
    {
        $this->isShop5Compatible = $isShop5Compatible;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    public function getMinShopVersion(): Version
    {
        return $this->minShopVersion ?? Version::parse('0.0.0');
    }

    public function setMinShopVersion(Version $minShopVersion): void
    {
        $this->minShopVersion = $minShopVersion;
    }

    public function getMaxShopVersion(): Version
    {
        return $this->maxShopVersion ?? Version::parse('0.0.0');
    }

    public function setMaxShopVersion(Version $maxShopVersion): void
    {
        $this->maxShopVersion = $maxShopVersion;
    }

    public function displayVersionRange(): string
    {
        $min = null;
        $max = null;
        if ($this->minShopVersion !== null && $this->minShopVersion->greaterThan('0.0.0')) {
            $min = (string)$this->minShopVersion;
        }
        if ($this->maxShopVersion !== null && $this->maxShopVersion->greaterThan('0.0.0')) {
            $max = (string)$this->maxShopVersion;
        }
        if ($min === null && $max !== null) {
            return '<= ' . $max;
        }
        if ($min !== null && $max === null) {
            return '>= ' . $min;
        }
        if ($min !== null && $max !== null) {
            return $min === $max ? $min : $min . ' &dash; ' . $max;
        }

        return '?';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getPluginID(): string
    {
        return $this->pluginID;
    }

    public function setPluginID(string $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    public function getID(): int
    {
        return $this->id;
    }

    public function setID(int $id): void
    {
        $this->id = $id;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function isHasError(): bool
    {
        return $this->hasError;
    }

    public function setHasError(bool $hasError): void
    {
        $this->hasError = $hasError;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    public function isInstalled(): bool
    {
        return $this->installed;
    }

    public function setInstalled(bool $installed): void
    {
        $this->installed = $installed;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function isLegacy(): bool
    {
        return $this->isLegacy;
    }

    public function setIsLegacy(bool $isLegacy): void
    {
        $this->isLegacy = $isLegacy;
    }

    public function isUpdateAvailable(): false|Version
    {
        return $this->updateAvailable;
    }

    public function setUpdateAvailable(false|Version $updateAvailable): void
    {
        $this->updateAvailable = $updateAvailable;
    }

    public function getUpdateFromDir(): ?string
    {
        return $this->updateFromDir;
    }

    public function setUpdateFromDir(?string $updateFromDir): void
    {
        $this->updateFromDir = $updateFromDir;
    }

    public function getDateInstalled(): ?DateTime
    {
        return $this->dateInstalled;
    }

    public function setDateInstalled(?DateTime $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    public function getLangVarCount(): int
    {
        return $this->langVarCount;
    }

    public function setLangVarCount(int $langVarCount): void
    {
        $this->langVarCount = $langVarCount;
    }

    public function hasLicenseCheck(): bool
    {
        return $this->hasLicenseCheck;
    }

    public function setHasLicenseCheck(bool $hasLicenseCheck): void
    {
        $this->hasLicenseCheck = $hasLicenseCheck;
    }

    public function getLicenseKey(): string
    {
        return $this->license;
    }

    public function setLicenseKey(string $license): void
    {
        $this->license = $license;
    }

    public function getLinkCount(): int
    {
        return $this->linkCount;
    }

    public function setLinkCount(int $linkCount): void
    {
        $this->linkCount = $linkCount;
    }

    public function getOptionsCount(): int
    {
        return $this->optionsCount;
    }

    public function setOptionsCount(int $optionsCount): void
    {
        $this->optionsCount = $optionsCount;
    }

    public function getReadmeMD(): ?string
    {
        return $this->readmeMD;
    }

    public function setReadmeMD(?string $readmeMD): void
    {
        $this->readmeMD = $readmeMD;
    }

    public function getLicenseMD(): ?string
    {
        return $this->licenseMD;
    }

    public function setLicenseMD(?string $licenseMD): void
    {
        $this->licenseMD = $licenseMD;
    }

    public function getExsID(): ?string
    {
        return $this->exsID;
    }

    public function setExsID(?string $exsID): void
    {
        $this->exsID = $exsID;
    }

    public function getPlugin(): ?PluginInterface
    {
        return $this->plugin;
    }

    public function setPlugin(?PluginInterface $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        $res = [];
        foreach (\get_object_vars($this) as $var => $val) {
            $res[$var] = $val;
        }

        return $res;
    }
}
