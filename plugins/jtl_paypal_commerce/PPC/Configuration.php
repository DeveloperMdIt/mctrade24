<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use Exception;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\frontend\ApplePayDAFController;
use Plugin\jtl_paypal_commerce\Repositories\ConfigurationRepository;

/**
 * Class Configuration
 * @package Plugin\jtl_paypal_commerce
 */
class Configuration
{
    /** @var static[] */
    protected static array $instance = [];

    protected ?ConfigurationRepository $repository = null;

    protected PluginInterface $plugin;

    protected static ?Collection $config = null;

    /** @var Collection[] */
    protected static array $items = [];

    protected ?ConfigValues $configValues = null;

    private const PREFIX = 'jtl_paypal_commerce_';

    public const CONSENT_ID = self::PREFIX . 'consent';

    /**
     * Configuration constructor.
     */
    protected function __construct(
        PluginInterface $plugin,
        DbInterface $db,
        ?ConfigurationRepository $repository = null,
        ?CryptoServiceInterface $cryptoService = null
    ) {
        $this->plugin       = $plugin;
        $this->repository   = $repository ?? new ConfigurationRepository($db, $plugin->getID());
        $this->configValues = new ConfigValues($this, $cryptoService ?? Shop::Container()->getCryptoService());

        static::$instance[static::class] = $this;
    }

    public static function getInstance(PluginInterface $plugin, DbInterface $db): static
    {
        return static::$instance[static::class] ?? new static($plugin, $db);
    }

    public function getRepository(): ConfigurationRepository
    {
        return $this->repository;
    }

    protected function loadConfig(bool $forceLoad = false): Collection
    {
        if (static::$config === null || $forceLoad) {
            static::$config = new Collection($this->repository->getConfig());
        }

        return static::$config;
    }

    protected function loadDefaults(Collection $config): Collection
    {
        return $config;
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function getConfigValues(): ConfigValues
    {
        return $this->configValues;
    }

    public function getConfigItemsByPrefix(?string $prefix = null): Collection
    {
        $prefix = $prefix ?? $this->getPrefix();

        if (!isset(static::$items[$prefix])) {
            static::$items[$prefix] = $this->loadDefaults($this->loadConfig())->filter(
                static function (string $value, string $key) use ($prefix) {
                    return \str_starts_with($key, $prefix);
                }
            )->mapWithKeys(static function (string $value, string $key) use ($prefix) {
                return [\str_replace($prefix, '', $key) => $value];
            });
        }

        return static::$items[$prefix];
    }

    public function getPrefixedConfigItem(string $name, mixed $default = null, ?string $prefix = null): ?string
    {
        $prefix      = $prefix ?? $this->getPrefix();
        $configItems = $this->getConfigItemsByPrefix($prefix);

        return $configItems[$name] ?? $default;
    }

    private function flushConfigCache(): void
    {
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_PLUGIN . '_' . $this->plugin->getID()]);
    }

    public function saveConfigItems(array $items, ?string $prefix = null): void
    {
        self::$config = null;
        $prefix       = $prefix ?? $this->getPrefix();

        foreach ($items as $key => $value) {
            $this->repository->delete($prefix . $key);
            if (isset(static::$items[$prefix])) {
                static::$items[$prefix]->forget($key);
            }
            if ($value !== '') {
                $this->repository->insert(
                    $prefix . $key,
                    \is_array($value) ? \implode(',', $value) : (string)$value
                );
                if (isset(static::$items[$prefix])) {
                    static::$items[$prefix]->put($key, $value);
                }
            }
        }
        $this->flushConfigCache();
    }

    /**
     * @param string[]    $items
     * @param string|null $prefix
     */
    public function deleteConfigItems(array $items, ?string $prefix = null): void
    {
        self::$config = null;
        $prefix       = $prefix ?? $this->getPrefix();

        foreach ($items as $value) {
            $this->repository->delete($prefix . $value);
            if (isset(static::$items[$prefix])) {
                static::$items[$prefix]->forget($value);
            }
        }
        $this->flushConfigCache();
    }

    public function getSectionDescription(string $section): string
    {
        $panelDescription = \__($section . '_description');
        if ($panelDescription === $section . '_description') {
            return '';
        }

        if ($section === Settings::BACKEND_SETTINGS_SECTION_APPLEPAYDISPLAY) {
            $path = \parse_url(Shop::getURL(), PHP_URL_PATH);
            if (\is_string($path) && !\in_array($path, ['', '/'])) {
                $panelDescription = \__($section . '_description_path');
                if ($panelDescription === $section . '_description_path') {
                    return '';
                }
            }
            $mode             = $this->getConfigValues()->getWorkingMode();
            $panelDescription = \str_replace(
                [
                    '%SHOP_DOMAIN%',
                    '%PAYPAL_DOMAIN%',
                    '%DAF_DOWNLOAD%',
                    '%DAF_URL%',
                ],
                [
                    \parse_url(Shop::getURL(), \PHP_URL_HOST),
                    $mode === ConfigValues::WORKING_MODE_SANDBOX ? 'www.sandbox.paypal.com' : 'www.paypal.com',
                    ApplePayDAFController::getDAFDownloadRoute(),
                    ApplePayDAFController::getDAFRoute(),
                ],
                $panelDescription
            );
        }

        return $panelDescription;
    }

    public function getSectionDescriptionType(array $settings): string
    {
        foreach ($settings as $setting) {
            if (\in_array($setting['value'], $setting['triggerWarn'] ?? [], true)) {
                return 'warning';
            }
        }

        return '';
    }

    private function maskValue(string $value, string $class): string
    {
        $pos     = 'pre';
        $partial = 0;
        if (\preg_match('/part-(pre|post)-(\d+)/', $class, $parts)) {
            $pos     = $parts[1] ?? 'pre';
            $partial = (int)($parts[2] ?? 0);
        }
        if ($partial > 0) {
            $value = $pos === 'pre'
                ? \substr($value, 0, $partial) . '...'
                : '...' . \substr($value, -$partial);
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    private function prepareSettings(): Collection
    {
        $storedConfig = $this->getConfigItemsByPrefix()->toArray();
        $workingMode  = $this->getConfigValues()->getWorkingMode();

        return BackendUIsettings::getDefaultSettings()->map(
            function ($item, $key) use ($storedConfig, $workingMode) {
                $wmProp  = $key . '_' . $workingMode;
                $handler = $item['handler'] ?? '';
                if (isset($storedConfig[$key])) {
                    $item['value'] = $storedConfig[$key];
                }
                if (isset($storedConfig[$wmProp])) {
                    $item['value'] = $storedConfig[$wmProp];
                }
                if ($handler !== '' && \is_a($handler, ConfigValueHandlerInterface::class, true)) {
                    $handlerInstance = new $handler();
                    $item['value']   = $handlerInstance->getValue($item['value']);
                }
                if ($item['type'] === 'partial_readonly') {
                    $item['value'] = $this->maskValue($item['value'], $item['class']);
                }

                return $item;
            }
        );
    }

    private function mapSingleSection(Collection $settings, string $singleSelection): array
    {
        $settingSections[$singleSelection]['settings'] = $settings
            ->filter(static function ($item) use ($singleSelection) {
                return $item['section'] === $singleSelection;
            })->sortBy(static function ($item) {
                return $item['sort'];
            })->toArray();
        $settingSections[$singleSelection]['heading']  = \__($singleSelection);

        return $settingSections;
    }

    public function mapSections(array $sections, ?array $exclude, Collection $settings): array
    {
        $settingSections = [];
        foreach ($sections as $section) {
            if (isset($exclude) && \in_array($section, $exclude, true)) {
                continue;
            }
            $settingSections[$section]['settings'] = $settings->filter(static function ($item) use ($section) {
                return $item['section'] === $section;
            })->sortBy(static function ($item) {
                return $item['sort'];
            })->toArray();
            $settingSections[$section]['heading']  = \__($section);
        }

        return $settingSections;
    }

    /**
     * @throws Exception
     */
    public function mapBackendSettings(?string $singleSection = null, ?array $exclude = null): array
    {
        $sections = Settings::BACKEND_SETTINGS_SECTIONS;
        $panels   = new Collection(Settings::BACKEND_SETTINGS_PANELS);
        $settings = $this->prepareSettings();

        if (isset($singleSection)) {
            return $this->mapSingleSection($settings, $singleSection);
        }

        $settingSections = $this->mapSections($sections, $exclude, $settings);

        $result = [];
        $panels = $panels->sortBy(static function ($item) {
            return $item === Settings::BACKEND_SETTINGS_PANEL_GENERAL ? 0 : 1;
        })->toArray();
        foreach ($panels as $panel) {
            foreach ($settingSections as $sectionKey => $section) {
                foreach ($section['settings'] as $settingName => $setting) {
                    if ($setting['panel'] === $panel) {
                        $result[$panel][$sectionKey]['settings'][$settingName] = $setting;
                        $result[$panel][$sectionKey]['heading']                = \__($sectionKey);
                    }
                }
            }
        }

        return $result;
    }

    public function mapFrontendSettings(
        ?string $singleSelection = null,
        ?array $exclude = null,
        ?array $include = null
    ): array {
        $configs      = $this->getConfigItemsByPrefix()->toArray();
        $mappedConfig = [];

        foreach ($configs as $key => $val) {
            foreach (Settings::BACKEND_SETTINGS_SECTIONS as $section) {
                $excludeCredentials = Settings::BACKEND_SETTINGS_SECTION_CREDENTIALS === $section;
                $excluded           = isset($exclude) && \in_array($section, $exclude, true);
                $notIncluded        = isset($include) && !\in_array($section, $include, true);
                //safety first, exclude credentials section by default
                if ($excludeCredentials || $excluded || $notIncluded) {
                    continue;
                }
                if (\str_contains($key, $section)) {
                    $property                          = \str_replace($section . '_', '', $key);
                    $mappedConfig[$section][$property] = $val;
                }
            }
        }

        // static configs, no user interaction
        $mappedConfig[Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS]['label'] = 'buynow';

        return $mappedConfig[$singleSelection] ?? $mappedConfig;
    }

    public function checkComponentVisibility(array $config, string $scope): bool
    {
        $pageConfig = 'showIn' . \ucfirst($scope);

        /** @noinspection IfReturnReturnSimplificationInspection */
        if (
            !isset($config['activate'], $config[$pageConfig]) ||
            $config['activate'] === 'N' || $config[$pageConfig] === 'N'
        ) {
            return false;
        }

        return true;
    }

    public function getAdminmenuSettingsId(string $setting = 'Einstellungen'): int
    {
        $tab = $this->plugin->getAdminMenu()->getItems()->first(function (object $menuItem) use ($setting) {
            return \strtoupper($menuItem->name) === \strtoupper($setting);
        });

        return $tab !== null ? $tab->id : 0;
    }

    public function getAdminmenuPanelId(string $panel, ?array $exclude = null): int
    {
        try {
            $panelId = \array_search(
                $panel,
                \array_keys($this->mapBackendSettings(null, $exclude)),
                true
            );
        } catch (Exception) {
            return 0;
        }

        return $panelId;
    }

    public function getWebhookId(): string
    {
        return $this->getPrefixedConfigItem('webhook_id', '');
    }

    public function getWebhookUrl(): string
    {
        return $this->getPrefixedConfigItem('webhook_url', '');
    }

    public function setWebhookId(string $webhookId): void
    {
        $this->saveConfigItems(['webhook_id' => $webhookId]);
    }

    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->saveConfigItems(['webhook_url' => $webhookUrl]);
    }

    public function removeWebhookId(): void
    {
        $this->deleteConfigItems(['webhook_id']);
    }

    public function removeWebhookUrl(): void
    {
        $this->deleteConfigItems(['webhook_url']);
    }
}
