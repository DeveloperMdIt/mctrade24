<?php

declare(strict_types=1);

namespace JTL\Plugin;

use DateTime;
use JTL\MagicCompatibilityTrait;

/**
 * Class PluginBC
 * @package JTL\Plugin
 */
abstract class PluginBC extends AbstractPlugin
{
    use MagicCompatibilityTrait;

    /**
     * @var array<string, string|string[]>
     */
    public static array $mapping = [
        'kPlugin'                         => 'ID',
        'nStatus'                         => 'State',
        'nVersion'                        => ['getMeta', 'Version'],
        'nPrio'                           => 'Priority',
        'cName'                           => ['getMeta', 'Name'],
        'cBeschreibung'                   => ['getMeta', 'Description'],
        'cAutor'                          => ['getMeta', 'Author'],
        'cURL'                            => ['getMeta', 'URL'],
        'cVerzeichnis'                    => ['getPaths', 'BaseDir'],
        'cPluginID'                       => 'PluginID',
        'cStoreID'                        => 'StoreID',
        'cLizenz'                         => ['getLicense', 'Key'],
        'cLizenzKlasse'                   => ['getLicense', 'Class'],
        'cLizenzKlasseName'               => ['getLicense', 'ClassName'],
        'cPluginPfad'                     => ['getPaths', 'VersionedPath'],
        'cFrontendPfad'                   => ['getPaths', 'FrontendPath'],
        'cFrontendPfadURL'                => ['getPaths', 'FrontendURL'],
        'cFrontendPfadURLSSL'             => ['getPaths', 'FrontendURL'],
        'cAdminmenuPfad'                  => ['getPaths', 'AdminPath'],
        'cAdminmenuPfadURL'               => ['getPaths', 'AdminURL'],
        'cAdminmenuPfadURLSSL'            => ['getPaths', 'AdminURL'],
        'cLicencePfad'                    => ['getPaths', 'LicencePath'],
        'oPluginSprachvariable_arr'       => ['getLocalization', 'LangVarsCompat'],
        'oPluginSprachvariableAssoc_arr'  => ['getLocalization', 'Translations'],
        'oPluginAdminMenu_arr'            => ['getAdminMenu', 'Items'],
        'oPluginFrontendLink_arr'         => ['getLinks', 'LinksCompat'],
        'oPluginZahlungsmethode_arr'      => ['getPaymentMethods', 'Methods'],
        'oPluginZahlungsmethodeAssoc_arr' => ['getPaymentMethods', 'MethodsAssoc'],
        'oPluginZahlungsKlasseAssoc_arr'  => ['getPaymentMethods', 'Classes'],
        'oPluginEmailvorlage_arr'         => ['getMailTemplates', 'Templates'],
        'oPluginEmailvorlageAssoc_arr'    => ['getMailTemplates', 'TemplatesAssoc'],
        'oPluginAdminWidget_arr'          => ['getWidgets', 'Widgets'],
        'oPluginAdminWidgetAssoc_arr'     => ['getWidgets', 'WidgetsAssoc'],
        'dZuletztAktualisiert'            => 'CompatDateUpdated',
        'dInstalliert'                    => 'CompatDateInstalled',
        'dErstellt'                       => 'CompatDateInstalled',
        'dZuletztAktualisiert_DE'         => 'CompatDateUpdatedGER',
        'dInstalliert_DE'                 => 'CompatDateInstalledGER',
        'dErstellt_DE'                    => 'CompatDateInstalledGER',
        'cPluginUninstallPfad'            => ['getPaths', 'Uninstaller'],
        'pluginCacheID'                   => ['getCache', 'ID'],
        'pluginCacheGroup'                => ['getCache', 'Group'],
        'cIcon'                           => ['getMeta', 'Icon'],
        'bBootstrap'                      => 'CompatBootstrap',
        'cTextReadmePath'                 => ['getMeta', 'ReadmeMD'],
        'cTextLicensePath'                => ['getMeta', 'LicenseMD'],
        'changelogPath'                   => ['getMeta', 'ChangelogMD']
    ];

    private int $calledHookID = -1;

    private bool $boostrap = false;

    public function getCompatDateUpdated(): string
    {
        return $this->getMeta()->getDateLastUpdate()->format('Y-m-d H:i:s');
    }

    public function setCompatDateUpdated(string $date): void
    {
        $this->getMeta()->setDateLastUpdate(new DateTime($date));
    }

    public function getCompatDateUpdatedGER(): string
    {
        return $this->getMeta()->getDateLastUpdate()->format('d.m.Y H:i');
    }

    public function setCompatDateUpdatedGER(string $date): void
    {
        $this->getMeta()->setDateLastUpdate(new DateTime($date));
    }

    public function getCompatDateInstalled(): string
    {
        return $this->getMeta()->getDateInstalled()->format('Y-m-d H:i');
    }

    public function setCompatDateInstalled(string $date): void
    {
        $this->getMeta()->setDateInstalled(new DateTime($date));
    }

    public function getCompatDateInstalledGER(): string
    {
        return $this->getMeta()->getDateInstalled()->format('d.m.Y H:i');
    }

    public function setCompatDateInstalledGER(string $date): void
    {
        $this->getMeta()->setDateInstalled(new DateTime($date));
    }

    public function getCompatBootstrap(): bool
    {
        return $this->boostrap;
    }

    public function setCompatBootstrap(mixed $d): void
    {
        $this->boostrap = (bool)$d;
    }

    public function getCalledHook(): int
    {
        return $this->calledHookID;
    }

    public function setCalledHook(int $id): void
    {
        $this->calledHookID = $id;
    }
}
