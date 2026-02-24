<?php

/** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace JTL\Backend;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Media\Image\Product;
use JTL\Media\Image\StatsItem;
use JTL\Settings\Option\Globals;
use JTL\Settings\Settings;
use JTL\Shop;
use stdClass;
use Systemcheck\Environment;
use Systemcheck\Platform\Hosting;
use Systemcheck\Platform\PDOConnection;
use Systemcheck\Tests\AbstractTest;

/**
 * Class Status
 * @package JTL\Backend
 * @deprecated since 5.5.0
 */
class Status
{
    private static ?Status $instance = null;

    /**
     * @deprecated since 5.5.0
     */
    public const CACHE_ID_FOLDER_PERMISSIONS = 'validFolderPermissions';

    /**
     * @deprecated since 5.5.0
     */
    public const CACHE_ID_DATABASE_STRUCT = 'validDatabaseStruct';

    /**
     * @deprecated since 5.5.0
     */
    public const CACHE_ID_MODIFIED_FILE_STRUCT = 'validModifiedFileStruct';

    /**
     * @deprecated since 5.5.0
     */
    public const CACHE_ID_ORPHANED_FILE_STRUCT = 'validOrphanedFilesStruct';

    /**
     * @deprecated since 5.5.0
     */
    public const CACHE_ID_EMAIL_SYNTAX_CHECK = 'validEMailSyntaxCheck';

    /**
     * @deprecated since 5.5.0
     */
    public const CACHE_ID_EXPORT_SYNTAX_CHECK = 'validExportSyntaxCheck';

    public function __construct(protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        self::$instance = $this;
    }

    public static function getInstance(
        DbInterface $db,
        ?JTLCacheInterface $cache = null,
        bool $flushCache = false
    ): self {
        $instance = self::$instance ?? new self($db, $cache ?? Shop::Container()->getCache());
        if ($flushCache) {
            $instance->cache->flushTags([\CACHING_GROUP_STATUS]);
        }

        return $instance;
    }

    /**
     * checks the db structure against 'admin/includes/shopmd5files/dbstruct_[shop-version].json'
     * @deprecated since 5.5.0
     */
    public function validDatabaseStruct(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function validModifiedFileStruct(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function validOrphanedFilesStruct(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function validFolderPermissions(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasPendingUpdates(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasActiveProfiler(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasInstallDir(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return array{db: string, php: string, diff: int}
     * @deprecated since 5.5.0
     */
    public function hasMysqlPhpTimeMismatch(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return [
            'db'   => '0',
            'php'  => '0',
            'diff' => 0
        ];
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasMobileTemplateIssue(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasStandardTemplateIssue(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasValidEnvironment(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasInstalledStandardLang(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getPlatform(): Hosting
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return new Hosting();
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getMySQLStats(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @return stdClass[]
     * @deprecated since 5.5.0
     */
    public function getOrphanedCategories(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasOrphanedCategories(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasFullTextIndexError(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasLicenseExpirations(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasNewPluginVersions(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getLocalizationProblems(bool $has = true): bool|Collection
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return $has ? false : new Collection();
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasInvalidPasswordResetMailTemplate(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasInsecureMailConfig(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function needPasswordRehash2FA(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getDuplicateLinkGroupTemplateNames(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getExportFormatErrorCount(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getEmailTemplateSyntaxErrorCount(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasExtensionSOAP(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getExtensions(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @deprecated since 5.5.0
     */
    public function hasDifferentTemplateVersion(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getPaymentMethodsWithError(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getObjectCache(): JTLCacheInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return $this->cache->setJtlCacheConfig(
            $this->db->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING)
        );
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getImageCache(): StatsItem
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return (new Product($this->db))->getStats();
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getSystemLogInfo(): stdClass
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $conf = Settings::intValue(Globals::SYSLOG_LEVEL);

        return (object)[
            'error'  => $conf >= \JTLLOG_LEVEL_ERROR,
            'notice' => $conf >= \JTLLOG_LEVEL_NOTICE,
            'debug'  => $conf >= \JTLLOG_LEVEL_NOTICE
        ];
    }

    /**
     * @deprecated since 5.5.0
     */
    public function getPluginSharedHooks(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return [];
    }

    /**
     * @return array<string, array<int, AbstractTest>>
     * @deprecated since 5.5.0
     */
    public function getEnvironmentTests(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        PDOConnection::getInstance()->setConnection($this->db->getPDO());

        return (new Environment())->executeTestGroup('Shop5');
    }
}
