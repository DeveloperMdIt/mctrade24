<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use JTL\Plugin\PluginInterface;
use Psr\Log\LoggerInterface;

class QueryValidator
{
    private PluginInterface $plugin;

    private ?LoggerInterface $logger = null;

    /**
     * @var string[]
     */
    private static array $regexBlock = [
        '(select\s*.+\s*from\s*.+)',
        '(update\s*.+\s*set\s*.+)',
        '(delete\s*.+\s*from\s*.+)',
        '(exec\s*.+)'
    ];

    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
        if (\method_exists($this->plugin, 'getLogger')) {
            $this->logger = $this->plugin->getLogger();
        }
    }

    private function getBlocklistItems(): array
    {
        return \explode(',', $this->plugin->getConfig()->getValue('blocklist'));
    }

    private function checkLength(string $query): bool
    {
        if (\mb_strlen($query) > $this->plugin->getConfig()->getValue('maxlength')) {
            $this->logger?->info('Blocked query {qry} - too long', ['qry' => $query]);

            return false;
        }

        return true;
    }

    private function checkBlockList(string $query): bool
    {
        foreach ($this->getBlocklistItems() as $item) {
            if (\mb_stripos($query, $item) !== false) {
                $this->logger?->info('Blocked query {qry} - hit keyword {kw}', ['qry' => $query, 'kw' => $item]);

                return false;
            }
        }

        return true;
    }

    private function checkRegEx(string $query): bool
    {
        foreach (self::$regexBlock as $regex) {
            if (\preg_match($regex, $query)) {
                $this->logger?->info('Blocked query {qry} - hit regex {rgx}', ['qry' => $query, 'rgx' => $regex]);

                return false;
            }
        }

        return true;
    }

    public function validate(string $query): bool
    {
        return $this->checkLength($query) && $this->checkBlockList($query) && $this->checkRegEx($query);
    }
}
