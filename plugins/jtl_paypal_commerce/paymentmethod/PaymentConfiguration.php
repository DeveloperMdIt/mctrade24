<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use Plugin\jtl_paypal_commerce\PPC\Configuration;

/**
 * Class PaymentConfiguration
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class PaymentConfiguration extends Configuration
{
    /** @var string */
    private string $prefix;

    /**
     * PaymentConfiguration constructor.
     * @param PluginInterface $plugin
     * @param DbInterface     $db
     */
    protected function __construct(PluginInterface $plugin, DbInterface $db)
    {
        parent::__construct($plugin, $db);

        $this->prefix = 'kPlugin_' . $plugin->getID() . '_';
    }

    /**
     * @inheritDoc
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
