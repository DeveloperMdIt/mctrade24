<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr\adminmenu;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Plugin\PluginInterface;
use JTL\Shop;

/**
 * Class Config
 * @package Plugin\jtl_gpsr\adminmenu
 */
class Config
{
    private PluginInterface $plugin;

    private DbInterface $db;

    /**
     * Config constructor
     * @param PluginInterface  $plugin
     * @param DbInterface|null $db
     */
    public function __construct(PluginInterface $plugin, ?DbInterface $db = null)
    {
        $this->plugin = $plugin;
        $this->db     = $db ?? Shop::Container()->getDB();
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getValue(string $key, $default = null)
    {
        $pluginConfig = $this->plugin->getConfig();
        $option       = $pluginConfig->getOption($key);
        if ($option !== null && $option->inputType === 'none') {
            $value = $this->db->select('xplugin_jtlgpsr_setting', 'key', $key);

            return $value !== null ? $value->value : $default;
        }

        return $pluginConfig->getValue($key) ?? $default;
    }

    public function getStringValue(string $key): string
    {
        $value = $this->getValue($key, '');

        return \is_string($value) ? $value : '';
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function storeValue(string $key, $value): void
    {
        $pluginConfig = $this->plugin->getConfig();
        $option       = $pluginConfig->getOption($key);
        if ($option !== null && $option->inputType === 'none') {
            $this->db->queryPrepared(
                'INSERT INTO xplugin_jtlgpsr_setting (`key`, `value`)
                    VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE
                        `value` = :value',
                ['key' => $key, 'value' => $value],
                ReturnType::DEFAULT
            );
        } else {
            $this->db->update(
                'tplugineinstellungen',
                ['kPlugin', 'cName'],
                [$this->plugin->getID(), $key],
                (object)['cWert' => $value]
            );
        }
    }
}
