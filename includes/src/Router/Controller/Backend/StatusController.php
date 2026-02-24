<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\StatusCheck\DBStructure;
use JTL\Backend\StatusCheck\Environment;
use JTL\Backend\StatusCheck\Factory;
use JTL\Backend\StatusCheck\FolderPermissions;
use JTL\Backend\StatusCheck\InstallDir;
use JTL\Backend\StatusCheck\Localization;
use JTL\Backend\StatusCheck\ModifiedFiles;
use JTL\Backend\StatusCheck\OrphanedCategories;
use JTL\Backend\StatusCheck\PendingUpdates;
use JTL\Backend\StatusCheck\PluginUpdates;
use JTL\Backend\StatusCheck\Profiler;
use JTL\Backend\StatusCheck\StatusCheckInterface;
use JTL\Cache\JTLCacheInterface;
use JTL\Checkout\ZahlungsLog;
use JTL\Media\Image\Product;
use JTL\Media\Image\StatsItem;
use JTL\Nice;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Systemcheck\Platform\PDOConnection;
use Systemcheck\Tests\AbstractTest;

/**
 * Class StatusController
 * @package JTL\Router\Controller\Backend
 */
class StatusController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::DIAGNOSTIC_VIEW);
        $this->getText->loadAdminLocale('notifications');
        $this->getText->loadAdminLocale('pages/status');
        $this->cache->flushTags([\CACHING_GROUP_STATUS]);

        return $smarty->assign('subscription', Shop::Container()->getJTLAPI()->getSubscription())
            ->assign('statusChecks', $this->getStatusChecks())
            ->assign('objectCache', $this->getObjectCache())
            ->assign('extensions', $this->getExtensions())
            ->assign('paymentMethods', $this->getPaymentMethodsWithError())
            ->assign('imageCache', $this->getImageCache())
            ->assign('environmentTests', $this->getEnvironmentTests())
            ->getResponse('status.tpl');
    }

    /**
     * @return StatusCheckInterface[]
     */
    private function getStatusChecks(): array
    {
        $factory      = new Factory($this->db, $this->cache, $this->baseURL . '/');
        $checkClasses = [
            DBStructure::class,
            ModifiedFiles::class,
            FolderPermissions::class,
            PendingUpdates::class,
            InstallDir::class,
            Profiler::class,
            Environment::class,
            OrphanedCategories::class,
            PluginUpdates::class,
            Localization::class,
        ];
        $result       = [];
        foreach ($checkClasses as $checkClass) {
            $result[] = $factory->getCheckByClassName($checkClass);
        }

        return $result;
    }

    private function getObjectCache(): JTLCacheInterface
    {
        return $this->cache->setJtlCacheConfig(
            $this->db->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING)
        );
    }

    private function getImageCache(): StatsItem
    {
        return (new Product($this->db))->getStats();
    }

    /**
     * @return array<string, array<int, AbstractTest>>
     */
    private function getEnvironmentTests(): array
    {
        PDOConnection::getInstance()->setConnection($this->db->getPDO());

        return (new \Systemcheck\Environment())->executeTestGroup('Shop5');
    }

    /**
     * @return \stdClass[]
     */
    private function getExtensions(): array
    {
        $nice       = Nice::getInstance($this->db, $this->cache);
        $extensions = $nice->gibAlleMoeglichenModule();
        foreach ($extensions as $extension) {
            $extension->bActive = $nice->checkErweiterung($extension->kModulId);
        }

        return $extensions;
    }

    /**
     * @return \stdClass[]
     */
    private function getPaymentMethodsWithError(): array
    {
        $incorrectPaymentMethods = [];
        $paymentMethods          = $this->db->selectAll(
            'tzahlungsart',
            'nActive',
            1,
            '*',
            'cAnbieter, cName, nSort, kZahlungsart'
        );
        foreach ($paymentMethods as $method) {
            $method->kZahlungsart = (int)$method->kZahlungsart;
            $method->nSort        = (int)$method->nSort;
            if (($logCount = ZahlungsLog::count($method->cModulId, \JTLLOG_LEVEL_ERROR)) > 0) {
                $method->logCount          = $logCount;
                $incorrectPaymentMethods[] = $method;
            }
        }

        return $incorrectPaymentMethods;
    }
}
