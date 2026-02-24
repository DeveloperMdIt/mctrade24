<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use InvalidArgumentException;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;
use Plugin\jtl_paypal_commerce\PPC\Environment\ProductionEnvironment;
use Plugin\jtl_paypal_commerce\PPC\Environment\SandboxEnvironment;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Class PPCHelper
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class PPCHelper
{
    /** @var Configuration|null */
    private static ?Configuration $config = null;

    /** @var EnvironmentInterface|null  */
    private static ?EnvironmentInterface $environment = null;

    /**
     * @param string $str
     * @param int    $maxLen
     * @param string $shortener
     * @return string
     */
    public static function shortenStr(string $str, int $maxLen, string $shortener = '...'): string
    {
        $strLen = \mb_strlen($str);
        if ($strLen <= $maxLen || $maxLen <= 0) {
            return $str;
        }

        $newStr = \mb_substr($str, 0, $maxLen - \mb_strlen($shortener));

        return $newStr . $shortener;
    }

    /**
     * @param string      $str
     * @param int         $minLen
     * @param int         $maxLen
     * @param string|null $regEx
     * @return string
     */
    public static function validateStr(string $str, int $minLen = 0, int $maxLen = 0, ?string $regEx = null): string
    {
        $len = \mb_strlen($str);
        if ($len < $minLen || ($maxLen > 0 && $len > $maxLen)) {
            throw new InvalidArgumentException(
                'Value must have [ ' . $minLen . '..' . ($maxLen > 0 ? $maxLen : '') . ' ] characters'
            );
        }

        if (empty($regEx)) {
            return $str;
        }
        if (!\str_starts_with($regEx, '/')) {
            $regEx = '/' . $regEx . '/';
        }
        if (!\preg_match($regEx, $str)) {
            throw new InvalidArgumentException('Value does not comply with the rule');
        }

        return $str;
    }

    /**
     * @param PluginInterface|null $plugin
     * @return Configuration
     */
    private static function createConfiguration(?PluginInterface $plugin = null): Configuration
    {
        $plugin = $plugin ?? PluginHelper::getPluginById('jtl_paypal_commerce');
        if ($plugin === null) {
            throw new RuntimeException('plugin jtl_paypal_commerce is not correctly installed');
        }

        return Configuration::getInstance($plugin, Shop::Container()->getDB());
    }

    /**
     * @param PluginInterface|null $plugin
     * @param bool                 $forceCreate
     * @return Configuration
     */
    public static function getConfiguration(?PluginInterface $plugin = null, bool $forceCreate = false): Configuration
    {
        if ($forceCreate) {
            return self::createConfiguration($plugin);
        }

        if (self::$config === null) {
            try {
                self::$config = Shop::Container()->get(Configuration::class);
            } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
                self::$config = self::createConfiguration($plugin);
            }
        }

        return self::$config;
    }

    public static function reloadConfiguration(): Configuration
    {
        self::$config = null;

        return self::getConfiguration();
    }

    /**
     * @param Configuration|null $config
     * @return EnvironmentInterface
     */
    private static function createEnvironment(?Configuration $config = null): EnvironmentInterface
    {
        $config       = $config ?? self::getConfiguration();
        $configValues = $config->getConfigValues();
        $clientType   = $configValues->getWorkingMode();
        $clientID     = $configValues->getClientID($clientType);
        $clientSecret = $configValues->getClientSecret($clientType);

        return $clientType === ConfigValues::WORKING_MODE_PRODUCTION
            ? new ProductionEnvironment($clientID, $clientSecret, \md5(\session_id()))
            : new SandboxEnvironment($clientID, $clientSecret, \md5(\session_id()));
    }

    public static function setEnvironment(?EnvironmentInterface $environment = null): void
    {
        self::$environment = $environment;
    }

    /**
     * @param Configuration|null $config
     * @param bool               $forceCreate
     * @return EnvironmentInterface
     */
    public static function getEnvironment(
        ?Configuration $config = null,
        bool $forceCreate = false
    ): EnvironmentInterface {
        if ($forceCreate) {
            return self::createEnvironment($config);
        }

        if (self::$environment === null) {
            try {
                self::$environment = Shop::Container()->get(EnvironmentInterface::class);
            } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
                self::$environment = self::createEnvironment($config);
            }
        }

        return self::$environment;
    }
}
