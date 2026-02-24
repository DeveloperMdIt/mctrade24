<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Utils;

use JTL\Shop;

class Logger
{
    public const LOG_PREFIX = '[Clerk]: ';

    public static function debug(string $message, array $context = []): void
    {
        Shop::Container()->getLogService()->debug(self::LOG_PREFIX . $message, $context);
    }

    public static function notice(string $message, array $context = []): void
    {
        Shop::Container()->getLogService()->notice(self::LOG_PREFIX . $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        Shop::Container()->getLogService()->error(self::LOG_PREFIX . $message, $context);
    }
}
