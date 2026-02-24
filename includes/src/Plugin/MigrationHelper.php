<?php

declare(strict_types=1);

namespace JTL\Plugin;

use DateTime;
use DirectoryIterator;
use Exception;
use JTL\DB\DbInterface;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
use JTL\Update\IMigration;
use Throwable;

/**
 * Class MigrationHelper
 * @package JTL\Plugin
 */
final class MigrationHelper
{
    private const MIGRATION_FILE_NAME_PATTERN = '/^Migration(\d{14}).php$/';

    public const MIGRATION_CLASS_NAME_PATTERN = '/Migration(\d{14})$/';

    public function __construct(private string $path, private readonly DbInterface $db)
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getIdFromFileName(string $fileName): ?int
    {
        $matches = [];

        return (\preg_match(self::MIGRATION_FILE_NAME_PATTERN, \basename($fileName), $matches))
            ? (int)$matches[1]
            : null;
    }

    /**
     * Returns names like 'Migration12345678901234'.
     *
     * @return class-string<IMigration>
     */
    public function mapFileNameToClassName(DirectoryIterator $file, string $pluginID): string
    {
        return \sprintf(
            'Plugin\%s\Migrations\%s',
            $pluginID,
            \str_replace('.' . $file->getExtension(), '', $file->getFilename())
        );
    }

    public function isValidMigrationFileName(string $fileName): int|bool
    {
        $matches = [];

        return \preg_match(self::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches);
    }

    /**
     * Check database integrity
     */
    public function verifyIntegrity(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS tpluginmigration 
            (
                kMigration bigint(14) NOT NULL, 
                nVersion int(3) NOT NULL, 
                pluginID varchar(255) NOT NULL, 
                dExecuted datetime NOT NULL,
                PRIMARY KEY (kMigration)
            ) ENGINE=InnoDB CHARACTER SET='utf8mb4' COLLATE='utf8mb4_unicode_ci'"
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS tmigrationlog 
            (
                kMigrationlog int(10) NOT NULL AUTO_INCREMENT, 
                kMigration bigint(20) NOT NULL, 
                cDir enum('up','down') NOT NULL, 
                cState varchar(6) NOT NULL, 
                cLog text NOT NULL, 
                dCreated datetime NOT NULL, 
                PRIMARY KEY (kMigrationlog)
            ) ENGINE=InnoDB CHARACTER SET='utf8mb4' COLLATE='utf8mb4_unicode_ci'"
        );
    }

    /**
     * @return \stdClass[]
     */
    public function indexColumns(string $idxTable, string $idxName): array
    {
        return $this->db->getObjects(
            'SHOW INDEXES FROM `' . $idxTable . '` WHERE Key_name = :idxName',
            ['idxName' => $idxName]
        );
    }

    /**
     * @param string[] $idxColumns
     */
    public function createIndex(string $idxTable, array $idxColumns, ?string $idxName = null, $idxUnique = false): bool
    {
        if (empty($idxName)) {
            $idxName = \implode('_', $idxColumns) . '_' . ($idxUnique ? 'UQ' : 'IDX');
        }

        if (\count($this->indexColumns($idxTable, $idxName)) === 0 || $this->dropIndex($idxTable, $idxName)) {
            $ddl = 'CREATE' . ($idxUnique ? ' UNIQUE' : '')
                . ' INDEX `' . $idxName . '` ON `' . $idxTable . '` '
                . '(`' . \implode('`, `', $idxColumns) . '`)';

            return (bool)$this->db->query($ddl);
        }

        return false;
    }

    public function dropIndex(string $idxTable, string $idxName): bool
    {
        if (\count($this->indexColumns($idxTable, $idxName)) > 0) {
            return (bool)$this->db->query('DROP INDEX `' . $idxName . '` ON `' . $idxTable . '` ');
        }

        return true;
    }

    public static function mapClassNameToId(string $className): ?int
    {
        $matches = [];

        return \preg_match(self::MIGRATION_CLASS_NAME_PATTERN, $className, $matches)
            ? (int)$matches[1]
            : null;
    }

    /**
     * @throws \SmartyException
     * @throws Exception
     */
    public static function create(string $pluginDir, string $description, string $author): string
    {
        $plugin = Shop::Container()->getDB()->select('tplugin', 'cVerzeichnis', $pluginDir);
        if ($plugin === null) {
            throw new Exception('There is no plugin for the given dir name.');
        }

        $datetime      = new DateTime('NOW');
        $timestamp     = $datetime->format('YmdHis');
        $filePath      = 'Migration' . $timestamp;
        $relPath       = \PLUGIN_DIR . $pluginDir . '/Migrations';
        $migrationPath = $relPath . '/' . $filePath . '.php';
        $fileSystem    = Shop::Container()->get(LocalFilesystem::class);
        try {
            $fileSystem->createDirectory($relPath);
        } catch (Throwable) {
            throw new Exception('Migrations path doesn\'t exist and could not be created!');
        }

        $content = Shop::Smarty()
            ->assign('description', $description)
            ->assign('author', $author)
            ->assign('created', $datetime->format(DateTime::RSS))
            ->assign('pluginDir', $pluginDir)
            ->assign('timestamp', $timestamp)
            ->fetch(\PFAD_ROOT . 'includes/src/Console/Command/Plugin/Template/migration.class.tpl');

        $fileSystem->write($migrationPath, $content);

        return $migrationPath;
    }
}
