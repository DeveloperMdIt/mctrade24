<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu;

use JTL\Backend\NotificationEntry;
use JTL\Helpers\Text;
use JTL\Plugin\PluginInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCaptureDecline;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCapturePending;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class TabInfoChecks
 * @package Plugin\jtl_paypal_commerce\adminmenu
 */
class TabInfoChecks
{
    /** @var PluginInterface */
    private PluginInterface $plugin;

    /**
     * @param PluginInterface $plugin
     */
    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param int $methodID
     * @return bool
     */
    public function isConnectable(int $methodID): bool
    {
        if (!PPCHelper::getConfiguration($this->plugin)->getConfigValues()->isAuthConfigured()) {
            return false;
        }

        $payMethod = Helper::getInstance($this->plugin)->getPaymentFromID($methodID);

        return $payMethod !== null && $payMethod->isValidIntern([
            'doOnlineCheck'       => true,
            'checkConnectionOnly' => true,
        ]);
    }

    /**
     * @param int      $methodID
     * @param string[] $info
     * @param string   $wrapper
     * @return string
     */
    public function getConnectionInfo(int $methodID, array $info = [], string $wrapper = '%s %s'): string
    {
        if ($this->isConnectable($methodID)) {
            return $info[NotificationEntry::TYPE_NONE] ?? '';
        }

        $payMethod = Helper::getInstance($this->plugin)->getPaymentFromID($methodID);
        if ($payMethod !== null) {
            $notification = $payMethod->getBackendNotification($this->plugin, true);
            if ($notification !== null && $notification->hasDescription()) {
                return \sprintf(
                    $wrapper,
                    Text::htmlentitiesOnce($notification->getDescription()),
                    $info[$notification->getType()] ?? ''
                );
            }
        }

        return $info[NotificationEntry::TYPE_DANGER] ?? '';
    }

    /**
     * @param int      $methodID
     * @param string[] $info
     * @param string   $wrapper
     * @return string
     */
    public function isShippmentLinked(int $methodID, array $info = [], string $wrapper = '%s %s'): string
    {
        $payMethod = Helper::getInstance($this->plugin)->getPaymentFromID($methodID);
        if ($payMethod !== null) {
            if (!$payMethod->isAssigned()) {
                return \sprintf(
                    $wrapper,
                    Text::htmlentitiesOnce(\__('Konfiguration mit Versandart fehlt')),
                    $info[NotificationEntry::TYPE_DANGER] ?? ''
                );
            }

            $notification = $payMethod->getBackendNotification($this->plugin, true);
            if ($notification !== null && $notification->hasDescription()) {
                return \sprintf(
                    $wrapper,
                    Text::htmlentitiesOnce($notification->getDescription()),
                    $info[$notification->getType()] ?? ''
                );
            }

            return $info[NotificationEntry::TYPE_NONE] ?? '';
        }

        return $info[NotificationEntry::TYPE_DANGER] ?? '';
    }

    /**
     * @param int         $methodId
     * @param string      $orderId
     * @param string|null $state
     * @param array       $info
     * @param string      $wrapper
     * @return string
     */
    public function getOrderState(
        int $methodId,
        string $orderId,
        ?string &$state,
        array $info = [],
        string $wrapper = '%s %s'
    ): string {
        $method = Helper::getInstance($this->plugin)->getPaymentFromID($methodId);
        $order  = null;
        if ($method !== null) {
            $order = (new TCCaptureDecline())->execute(
                $method,
                (new TCCapturePending())->execute($method, $method->getPPOrder($orderId))
            );
        }
        if ($order !== null) {
            $state = $method->getValidOrderState($order);

            return \sprintf(
                $wrapper,
                Text::htmlentitiesOnce(\__('STATE_' . $state)),
                $info[$state] ?? ($info['UNKNOWN'] ?? '')
            );
        }

        return \sprintf(
            $wrapper,
            Text::htmlentitiesOnce(\__('STATE_ORDER_NOT_FOUND' . $state)),
            $info['UNKNOWN'] ?? ''
        );
    }
}
