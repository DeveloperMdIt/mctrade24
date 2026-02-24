<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use JTL\Pagination\Pagination;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\PendingOrders;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\Repositories\PendingOrdersRepository;

/**
 * Class PendingOrders
 * @package Plugin\jtl_paypal_commerce\adminmenu\Renderer
 */
class PendingOrdersRenderer extends AbstractRenderer
{
    private ?PendingOrdersRepository $repository;

    /**
     * @inheritDoc
     */
    public function __construct(
        PluginInterface $plugin,
        ?Configuration $config = null,
        ?Logger $logger = null,
        ?PendingOrdersRepository $repository = null
    ) {
        parent::__construct($plugin, $config, $logger);

        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function render(JTLSmarty $smarty): void
    {
        $this->checkRendering();

        $pagination    = new Pagination();
        $pendingOrders = (new PendingOrders(
            $this->getPlugin(),
            Shop::Container()->getDB(),
            $this->repository
        ))->getPendingOrders();
        $pagination->setItemArray($pendingOrders);

        $smarty->assign('pagination', $pagination->assemble());
    }
}
