<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

use JTL\DB\DbInterface;
use JTL\MagicCompatibilityTrait;
use JTL\Plugin\PluginInterface;
use stdClass;

/**
 * Class PaymentMethod
 * @package JTL\Plugin\Data
 */
class PaymentMethod
{
    use MagicCompatibilityTrait;

    private int $methodID = 0;

    private string $name = '';

    private string $moduleID = '';

    /**
     * @var int[]
     */
    private array $customerGroups = [];

    private string $template = '';

    private string $templateFilePath = '';

    private string $additionalTemplate = '';

    private string $image = '';

    private int $sort = 0;

    private bool $sendMail = false;

    private bool $active = false;

    private string $provider = '';

    private string $tsCode = '';

    private bool $duringOrder = false;

    private bool $useCurl = false;

    private bool $useSoap = false;

    private bool $useSockets = false;

    private bool $usable = false;

    private int $pluginID = 0;

    private string $classFile = '';

    /**
     * @var class-string
     */
    private string $className = '';

    private string $templatePath = '';

    /**
     * @var stdClass[]
     */
    private array $config = [];

    /**
     * @var array<int, stdClass>
     */
    private array $localization = [];

    private string $classFilePath = '';

    /**
     * @var array<string, string>
     */
    public static array $mapping = [
        'kZahlungsart'                    => 'MethodID',
        'cName'                           => 'Name',
        'cModulId'                        => 'ModuleID',
        'cKundengruppen'                  => 'CustomerGroups',
        'cPluginTemplate'                 => 'Template',
        'cZusatzschrittTemplate'          => 'AdditionalTemplate',
        'cBild'                           => 'Image',
        'nSort'                           => 'Sort',
        'nMailSenden'                     => 'SendMail',
        'nActive'                         => 'Active',
        'cAnbieter'                       => 'Provider',
        'cTSCode'                         => 'TsCode',
        'nWaehrendBestellung'             => 'DuringOrder',
        'nCURL'                           => 'UseCurl',
        'nSOAP'                           => 'UseSoap',
        'nSOCKETS'                        => 'UseSockets',
        'nNutzbar'                        => 'Usable',
        'kPlugin'                         => 'PluginID',
        'cClassPfad'                      => 'ClassFile',
        'cClassName'                      => 'ClassName',
        'cTemplatePfad'                   => 'TemplatePath',
        'oZahlungsmethodeEinstellung_arr' => 'Config',
        'oZahlungsmethodeSprache_arr'     => 'Localization',
        'cTemplateFileURL'                => 'TemplateFilePath',
    ];

    public function __construct(?stdClass $data = null, ?PluginInterface $plugin = null)
    {
        if ($data !== null && \SAFE_MODE === false) {
            $this->mapData($data, $plugin);
        }
    }

    public static function load(DbInterface $db, string $moduleId): self
    {
        $data = $db->selectSingleRow('tzahlungsart', 'cModulId', $moduleId);
        if ($data !== null) {
            $data->kZahlungsart        = (int)$data->kZahlungsart;
            $data->nSort               = (int)$data->nSort;
            $data->nMailSenden         = (int)$data->nMailSenden;
            $data->nActive             = (int)$data->nActive;
            $data->nCURL               = (int)$data->nCURL;
            $data->nSOAP               = (int)$data->nSOAP;
            $data->nSOCKETS            = (int)$data->nSOCKETS;
            $data->nNutzbar            = (int)$data->nNutzbar;
            $data->nWaehrendBestellung = (int)$data->nWaehrendBestellung;
        }

        return new self($data);
    }

    public function mapData(stdClass $data, ?PluginInterface $plugin = null): void
    {
        foreach (\get_object_vars($data) as $item => $value) {
            $method = self::$mapping[$item] ?? null;
            if ($method === null) {
                continue;
            }
            $method = 'set' . $method;
            $this->$method($value);
        }
        if ($plugin === null) {
            return;
        }
        $this->classFilePath = $plugin->getPaths()->getVersionedPath() . \PFAD_PLUGIN_PAYMENTMETHOD . $this->classFile;
        if (\file_exists($this->classFilePath)) {
            global $oPlugin;
            $oPlugin = $plugin;
            require_once $this->classFilePath;
            if (!\class_exists($this->className)) {
                $class = \sprintf(
                    'Plugin\\%s\\%s\\%s',
                    $plugin->getPluginID(),
                    \rtrim(\PFAD_PLUGIN_PAYMENTMETHOD, '/'),
                    $this->className
                );
                if (\class_exists($class)) {
                    $this->className = $class;
                }
            }
        }
    }

    public function getMethodID(): int
    {
        return $this->methodID;
    }

    public function setMethodID(int $methodID): void
    {
        $this->methodID = $methodID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getModuleID(): string
    {
        return $this->moduleID;
    }

    public function setModuleID(string $moduleID): void
    {
        $this->moduleID = $moduleID;
    }

    /**
     * @return int[]
     */
    public function getCustomerGroups(): array
    {
        return $this->customerGroups;
    }

    /**
     * @param string|int[] $customerGroups
     */
    public function setCustomerGroups(array|string $customerGroups): void
    {
        if (\is_array($customerGroups)) {
            $this->customerGroups = $customerGroups;

            return;
        }

        $this->customerGroups = \array_map('\intval', \array_filter(\explode(';', $customerGroups)));
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getTemplateFilePath(): string
    {
        return $this->templateFilePath;
    }

    public function setTemplateFilePath(string $templateFilePath): void
    {
        $this->templateFilePath = $templateFilePath;
    }

    public function getAdditionalTemplate(): string
    {
        return $this->additionalTemplate;
    }

    public function setAdditionalTemplate(string $additionalTemplate): void
    {
        $this->additionalTemplate = $additionalTemplate;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function getSendMail(): bool
    {
        return $this->sendMail;
    }

    public function setSendMail(bool|int $sendMail): void
    {
        $this->sendMail = (bool)$sendMail;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool|int $active): void
    {
        $this->active = (bool)$active;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getTsCode(): string
    {
        return $this->tsCode;
    }

    public function setTsCode(string $tsCode): void
    {
        $this->tsCode = $tsCode;
    }

    public function getDuringOrder(): bool
    {
        return $this->duringOrder;
    }

    public function setDuringOrder(bool|int $duringOrder): void
    {
        $this->duringOrder = (bool)$duringOrder;
    }

    public function getUseCurl(): bool
    {
        return $this->useCurl;
    }

    public function setUseCurl(bool|int $useCurl): void
    {
        $this->useCurl = (bool)$useCurl;
    }

    public function getUseSoap(): bool
    {
        return $this->useSoap;
    }

    public function setUseSoap(bool|int $useSoap): void
    {
        $this->useSoap = (bool)$useSoap;
    }

    public function getUseSockets(): bool
    {
        return $this->useSockets;
    }

    public function setUseSockets(bool|int $useSockets): void
    {
        $this->useSockets = (bool)$useSockets;
    }

    public function getUsable(): bool
    {
        return $this->usable;
    }

    public function setUsable(bool|int $usable): void
    {
        $this->usable = (bool)$usable;
    }

    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    public function getClassFile(): string
    {
        return $this->classFile;
    }

    public function setClassFile(string $classFile): void
    {
        $this->classFile = $classFile;
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param class-string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }

    /**
     * @return stdClass[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param stdClass[] $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return array<int, stdClass>
     */
    public function getLocalization(): array
    {
        return $this->localization;
    }

    /**
     * @param array<int, stdClass> $localization
     */
    public function setLocalization(array $localization): void
    {
        $this->localization = $localization;
    }

    public function getClassFilePath(): string
    {
        return $this->classFilePath;
    }

    public function setClassFilePath(string $classFilePath): void
    {
        $this->classFilePath = $classFilePath;
    }
}
