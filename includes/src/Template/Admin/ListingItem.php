<?php

declare(strict_types=1);

namespace JTL\Template\Admin;

use DateTime;
use InvalidArgumentException;
use JTL\Backend\FileCheck;
use JTL\Plugin\State;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTLShop\SemVer\Version;

/**
 * Class ListingItem
 * @package JTL\Template\Admin
 */
class ListingItem
{
    private string $path = '';

    private string $dir = '';

    private string $name = '';

    private Version $version;

    private Version $maxShopVersion;

    private Version $minShopVersion;

    private string $description = '';

    private string $author = '';

    private ?string $preview = '';

    private ?string $url = '';

    private int $id = 0;

    private ?string $framework = '';

    private ?string $exsid = '';

    private int $errorCode = 0;

    private string $errorMessage = '';

    private bool $hasError = false;

    private bool $available = true;

    private bool $active = false;

    private int $state = State::NONE;

    private Version|false $updateAvailable = false;

    private bool $hasLicenseCheck = false;

    private bool $isChild = false;

    private string $license = '';

    private ?string $updateFromDir;

    private ?DateTime $dateInstalled;

    private int $langVarCount = 0;

    private int $linkCount = 0;

    private int $optionsCount = 0;

    private ?string $readmeMD;

    private ?string $licenseMD;

    private ?string $parent;

    /**
     * @var \stdClass[]|bool|null
     */
    private array|bool|null $checksums = null;

    private bool $isPreview = false;

    public function __construct()
    {
        $this->version        = Version::parse('0.0.0');
        $this->maxShopVersion = Version::parse('0.0.0');
        $this->minShopVersion = Version::parse('0.0.0');
    }

    /**
     * @param array<mixed> $xml
     */
    public function parseXML(array $xml, int $validationResult): self
    {
        $this->name = $xml['cVerzeichnis'];
        $this->dir  = $xml['cVerzeichnis'];
        $node       = $xml['Template'][0] ?? null;
        if ($validationResult !== TemplateValidator::RES_OK) {
            return $this->fail($validationResult);
        }
        if ($node !== null) {
            $this->name           = $node['Name'];
            $this->description    = $node['Description'] ?? '';
            $this->exsid          = $node['ExsID'] ?? '';
            $this->author         = $node['Author'] ?? '';
            $this->url            = $node['URL'] ?? null;
            $this->preview        = $node['Preview'] ?? null;
            $this->framework      = $node['Framework'] ?? null;
            $this->isChild        = isset($node['Parent']);
            $this->parent         = $node['Parent'] ?? null;
            $version              = $node['Version'] ?? $node['ShopVersion'];
            $this->optionsCount   = ($this->isChild() || isset($node['Settings'][0])) ? 1 : 0;
            $this->maxShopVersion = Version::parse($node['MaxShopVersion'] ?? '0.0.0');
            $this->minShopVersion = Version::parse($node['MinShopVersion'] ?? $node['ShopVersion'] ?? '5.0.0');
            $this->addChecksums();
            try {
                $this->version = Version::parse($version);
            } catch (InvalidArgumentException) {
                $xml['cFehlercode'] = TemplateValidator::RES_SHOP_VERSION_NOT_FOUND;
            }
        }
        if ($xml['cFehlercode'] !== TemplateValidator::RES_OK) {
            return $this->fail($xml['cFehlercode']);
        }

        return $this;
    }

    private function generateErrorMessage(int $code): void
    {
        $msg = match ($code) {
            TemplateValidator::RES_OK                     => '',
            TemplateValidator::RES_PARENT_NOT_FOUND       => \__('errorParentNotFound'),
            TemplateValidator::RES_SHOP_VERSION_NOT_FOUND => \__('errorShopVersionNotFound'),
            TemplateValidator::RES_XML_NOT_FOUND          => \__('errorXmlNotFound'),
            TemplateValidator::RES_XML_PARSE_ERROR        => \__('errorXmlParse'),
            TemplateValidator::RES_NAME_NOT_FOUND         => \__('errorNameNotFound'),
            TemplateValidator::RES_INVALID_VERSION        => \__('errorInvalidVersion'),
            TemplateValidator::RES_INVALID_NAMESPACE      => \__('errorInvalidNamespace'),
            default                                       => \__('errorUnknown'),
        };
        $this->setErrorMessage($msg);
    }

    private function addChecksums(): void
    {
        $files       = [];
        $errorsCount = 0;
        $base        = \PFAD_ROOT . \PFAD_TEMPLATES . \basename($this->dir) . '/';
        $checker     = new FileCheck();
        $res         = $checker->validateCsvFile($base . 'checksums.csv', $files, $errorsCount, $base);
        if ($res === FileCheck::ERROR_INPUT_FILE_MISSING || $res === FileCheck::ERROR_NO_HASHES_FOUND) {
            $this->setChecksums(null);

            return;
        }
        $this->setChecksums($errorsCount === 0 ? true : $files);
    }

    private function fail(int $errorCode): self
    {
        $this->setAvailable(false);
        $this->setHasError(true);
        $this->setErrorCode($errorCode);
        $this->generateErrorMessage($errorCode);

        return $this;
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

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): void
    {
        $this->preview = $preview;
    }

    public function getFramework(): ?string
    {
        return $this->framework;
    }

    public function setFramework(?string $framework): void
    {
        $this->framework = $framework;
    }

    public function getExsID(): ?string
    {
        return $this->exsid;
    }

    public function setExsID(?string $exsid): void
    {
        $this->exsid = $exsid;
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

    public function hasError(): bool
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function isUpdateAvailable(): bool|Version
    {
        return $this->updateAvailable;
    }

    public function setUpdateAvailable(bool|Version $updateAvailable): void
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

    public function getMaxShopVersion(): Version
    {
        return $this->maxShopVersion;
    }

    public function setMaxShopVersion(Version $maxShopVersion): void
    {
        $this->maxShopVersion = $maxShopVersion;
    }

    public function getMinShopVersion(): Version
    {
        return $this->minShopVersion;
    }

    public function setMinShopVersion(Version $minShopVersion): void
    {
        $this->minShopVersion = $minShopVersion;
    }

    public function displayVersionRange(): string
    {
        $min = $this->minShopVersion->greaterThan('0.0.0')
            ? (string)$this->minShopVersion
            : null;
        $max = $this->maxShopVersion->greaterThan('0.0.0')
            ? (string)$this->maxShopVersion
            : null;
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

    public function getURL(): ?string
    {
        return $this->url;
    }

    public function setURL(?string $url): void
    {
        $this->url = $url;
    }

    public function isChild(): bool
    {
        return $this->isChild;
    }

    public function setIsChild(bool $isChild): void
    {
        $this->isChild = $isChild;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(?string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return \stdClass[]|bool|null
     */
    public function getChecksums(): bool|array|null
    {
        return $this->checksums;
    }

    /**
     * @param bool|\stdClass[]|null $checksums
     */
    public function setChecksums(bool|array|null $checksums): void
    {
        $this->checksums = $checksums;
    }

    public function isPreview(): bool
    {
        return $this->isPreview;
    }

    public function setIsPreview(bool $isPreview): void
    {
        $this->isPreview = $isPreview;
    }
}
