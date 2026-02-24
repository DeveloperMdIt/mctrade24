<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Backend;

use JTL\DB\DbInterface;
use JTL\Helpers\Text;
use JTL\Plugin\PluginInterface;
use JTL\Smarty\JTLSmarty;
use SmartyException;

/**
 * Class CustomLink
 * @package Plugin\jtl_google_shopping\Backend
 */
abstract class CustomLink
{
    protected PluginInterface $plugin;

    protected DbInterface $db;

    protected array $requestData;

    /**
     * @param PluginInterface $plugin
     * @param DbInterface     $db
     * @param array           $requestData
     */
    private function __construct(PluginInterface $plugin, DbInterface $db, array $requestData = [])
    {
        $this->plugin      = $plugin;
        $this->db          = $db;
        $this->requestData = Text::filterXSS($requestData);
    }

    /**
     * @param PluginInterface $plugin
     * @param DbInterface     $db
     * @param array           $requestData
     * @return static
     */
    public static function handleRequest(PluginInterface $plugin, DbInterface $db, array $requestData = []): self
    {
        $instance = new static($plugin, $db, $requestData);
        $instance->controller();

        return $instance;
    }

    /**
     * @return bool
     */
    abstract protected function controller(): bool;

    /**
     * @return string
     */
    abstract protected function getTemplate(): string;

    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    /**
     * @param array $requestData
     */
    public function setRequestData(array $requestData): void
    {
        $this->requestData = $requestData;
    }

    /**
     * @param string     $value
     * @param mixed|null $default
     * @return mixed
     */
    public function getRequestValue(string $value, $default = null)
    {
        $data = $this->getRequestData();

        return \array_key_exists($value, $data) ? $data[$value] : $default;
    }

    public function display(JTLSmarty $smarty): string
    {
        try {
            return $smarty->assign('requestData', $this->getRequestData())
                ->assign('kPlugin', $this->getPlugin()->getID())
                ->fetch($this->getPlugin()->getPaths()->getAdminPath() . $this->getTemplate());
        } catch (SmartyException $e) {
            return $e->getMessage();
        }
    }
}
