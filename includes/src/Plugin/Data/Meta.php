<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

use DateTime;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class Meta
 * @package JTL\Plugin\Data
 */
class Meta
{
    private ?string $name;

    private ?string $description = null;

    private ?string $author = null;

    private ?string $url = null;

    private ?string $icon = null;

    private ?string $readmeMD = null;

    private ?string $licenseMD = null;

    private ?string $changelogMD = null;

    private ?DateTime $dateLastUpdate = null;

    private ?DateTime $dateInstalled = null;

    private string|int $version;

    private ?Version $semVer = null;

    private Version|false $updateAvailable = false;

    private ?string $exsID = null;

    public function loadDBMapping(stdClass $data): self
    {
        $msgid                = $data->cPluginID . '_desc';
        $desc                 = \__($msgid);
        $this->description    = $desc === $msgid ? \__($data->cBeschreibung) : $desc;
        $this->author         = \__($data->cAutor);
        $this->name           = \__($data->cName);
        $this->url            = \__($data->cURL);
        $this->icon           = $data->cIcon;
        $this->dateInstalled  = new DateTime($data->dInstalliert === 'NOW()' ? 'now' : $data->dInstalliert);
        $this->dateLastUpdate = new DateTime(
            $data->dZuletztAktualisiert === 'NOW()' ? 'now' : $data->dZuletztAktualisiert
        );
        $this->version        = $data->nVersion;
        $this->semVer         = Version::parse($this->version);
        $this->exsID          = $data->exsID;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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

    public function getURL(): string
    {
        return $this->url;
    }

    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getReadmeMD(): ?string
    {
        return $this->readmeMD;
    }

    public function setReadmeMD(string $readmeMD): void
    {
        $this->readmeMD = $readmeMD;
    }

    public function getLicenseMD(): ?string
    {
        return $this->licenseMD;
    }

    public function setLicenseMD(string $licenseMD): void
    {
        $this->licenseMD = $licenseMD;
    }

    public function getChangelogMD(): ?string
    {
        return $this->changelogMD;
    }

    public function setChangelogMD(string $changelogMD): void
    {
        $this->changelogMD = $changelogMD;
    }

    public function getDateLastUpdate(): DateTime
    {
        return $this->dateLastUpdate;
    }

    public function setDateLastUpdate(DateTime $dateLastUpdate): void
    {
        $this->dateLastUpdate = $dateLastUpdate;
    }

    public function getDateInstalled(): DateTime
    {
        return $this->dateInstalled;
    }

    public function setDateInstalled(DateTime $dateInstalled): void
    {
        $this->dateInstalled = $dateInstalled;
    }

    public function getVersion(): int|string
    {
        return $this->version;
    }

    public function setVersion(int|string $version): void
    {
        $this->version = $version;
    }

    public function getSemVer(): Version
    {
        return $this->semVer;
    }

    public function setSemVer(Version $semVer): void
    {
        $this->semVer = $semVer;
    }

    public function isUpdateAvailable(): bool
    {
        return \is_bool($this->updateAvailable) ? $this->updateAvailable : $this->updateAvailable !== null;
    }

    public function setUpdateAvailable(false|Version $updateAvailable): void
    {
        $this->updateAvailable = $updateAvailable;
    }

    public function getUpdateAvailable(): false|Version
    {
        return $this->updateAvailable;
    }

    public function getExsID(): ?string
    {
        return $this->exsID;
    }

    public function setExsID(?string $exsID): void
    {
        $this->exsID = $exsID;
    }
}
