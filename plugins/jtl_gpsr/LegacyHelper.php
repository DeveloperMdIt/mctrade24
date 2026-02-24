<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr;

use JTL\Filesystem\Filesystem;
use League\Flysystem\FilesystemException;

/**
 * Class LegacyHelper
 * @package Plugin\jtl_gpsr
 */
class LegacyHelper
{
    /**
     * @throws FilesystemException
     */
    public static function directoryExists(Filesystem $fs, string $location): bool
    {
        if (method_exists($fs, 'directoryExists')) {
            return $fs->directoryExists($location);
        }

        return $fs->has($location);
    }

    /**
     * @throws FilesystemException
     */
    public static function createDirectory(Filesystem $fs, string $location, array $config = []): void
    {
        if (method_exists($fs, 'createDirectory')) {
            $fs->createDirectory($location, $config);
        } else {
            $fs->createDir($location, $config);
        }
    }

    /**
     * @throws FilesystemException
     */
    public static function deleteDirectory(Filesystem $fs, string $location): void
    {
        if (method_exists($fs, 'deleteDirectory')) {
            $fs->deleteDirectory($location);
        } else {
            $fs->deleteDir($location);
        }
    }
}
