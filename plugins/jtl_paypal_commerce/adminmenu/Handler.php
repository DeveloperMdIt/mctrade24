<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu;

use Exception;
use JTL\Backend\NotificationEntry;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\IO\IOResponse;
use JTL\Language\LanguageHelper;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\TwoFA\BackendUserData;
use Plugin\jtl_paypal_commerce\adminmenu\Controller\ResetCredentialsController;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\CredentialCode;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

use function Functional\first;

/**
 * Class Handler
 * @package Plugin\jtl_paypal_commerce\adminmenu
 */
class Handler
{
    /** @var PluginInterface */
    private PluginInterface $plugin;

    /** @var DbInterface */
    private DbInterface $db;

    /**
     * Handler constructor.
     * @param PluginInterface  $plugin
     * @param DbInterface|null $db
     */
    public function __construct(PluginInterface $plugin, ?DbInterface $db = null)
    {
        $this->plugin = $plugin;
        $this->db     = $db ?? Shop::Container()->getDB();
    }

    /**
     * @return void
     */
    public function smarty(): void
    {
    }

    /**
     * @param string $tplElement
     * @return IOResponse
     * @noinspection PhpUnused
     */
    public function handleAjax(string $tplElement): IOResponse
    {
        $results             = [
            NotificationEntry::TYPE_DANGER  => '<i class="fa fa-times text-danger"></i>',  // red cross
            NotificationEntry::TYPE_WARNING => '<i class="fa fa-check text-warning"></i>', // orange hook
            NotificationEntry::TYPE_NONE    => '<i class="fa fa-check text-success"></i>', // green hook
        ];
        $resultWrap          = '<span data-html="true" data-toggle="tooltip" data-placement="left"
                title="" data-original-title="%s">%s</span>';
        $displayResult       = $results[NotificationEntry::TYPE_DANGER];
        [$paymentID, $tplID] = \explode('_', $tplElement);
        $infoCheck           = new TabInfoChecks($this->plugin);

        if (($tplID === 'payment-linked')) {
            $displayResult = $infoCheck->isShippmentLinked((int)$paymentID, $results, $resultWrap);
        }
        if (($tplID === 'ppc-connectable')) {
            $displayResult = $infoCheck->getConnectionInfo((int)$paymentID, $results, $resultWrap);
        }
        $response = new IOResponse();
        $response->assignDom(
            $paymentID . '_' . $tplID,
            'innerHTML',
            $displayResult
        );

        return $response;
    }

    /**
     * @param string $query
     * @param int    $limit
     * @return string
     */
    public function handleCarrierMapping(string $query, int $limit): string
    {
        $results = $this->db->getObjects(
            'SELECT DISTINCT cLogistiker AS cName
                FROM tbestellung
                WHERE cLogistiker LIKE :search' .
            ($limit > 0 ? ' LIMIT ' . $limit : ''),
            [
                'search' => '%' . $query . '%',
            ]
        );

        try {
            return \json_encode($results, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '';
        }
    }

    /**
     * @param int    $methodId
     * @param string $txnId
     * @return IOResponse
     */
    public function handleOrderState(int $methodId, string $txnId): IOResponse
    {
        $infos   = [
            OrderStatus::STATUS_COMPLETED             => '<i class="fa fa-check text-success"></i>',  // green hook
            OrderStatus::STATUS_PENDING               => '<i class="fa fa-clock text-warning"></i>',  // orange clock
            OrderStatus::STATUS_DECLINED              => '<i class="fa fa-ban text-danger"></i>',     // red ban
            OrderStatus::STATUS_APPROVED              => '<i class="fa fa-thumbs-up text-info"></i>', // gray thumbs up
            OrderStatus::STATUS_CREATED               => '<i class="fa fa-user text-warning"></i>',   // orange user
            OrderStatus::STATUS_PAYER_ACTION_REQUIRED => '<i class="fa fa-user text-warning"></i>',   // orange user
            'UNKNOWN' => '<i class="fa fa-question-circle text-info"></i>', // grey question mark
        ];
        $wrapper = '<span data-html="true" data-toggle="tooltip" data-placement="left"
                title="" data-original-title="%s">%s</span>';

        $response  = new IOResponse();
        $infoCheck = new TabInfoChecks($this->plugin);
        $state     = null;
        $response
            ->assignDom($txnId, 'innerHTML', $infoCheck->getOrderState($methodId, $txnId, $state, $infos, $wrapper))
            ->assignDom('reload-' . $txnId, 'disabled', false)
            ->assignDom('delete-' . $txnId, 'disabled', false)
            ->assignDom('apply-' . $txnId, 'disabled', !\in_array($state ?? '', [
                OrderStatus::STATUS_COMPLETED,
                OrderStatus::STATUS_DECLINED,
            ], true));

        return $response;
    }

    /**
     * @param IOResponse $response
     * @param JTLSmarty  $smarty
     * @param string     $id
     * @return void
     */
    private function listActionAPMSettingsGet(IOResponse $response, JTLSmarty $smarty, string $id): void
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/zahlungsarten');
        $config       = PPCHelper::getConfiguration($this->plugin);
        $allLang      = LanguageHelper::getAllLanguages(0, true, true);
        $descriptions = [];

        foreach ($allLang as $languageModel) {
            $iso                = \strtoupper($languageModel->getIso());
            $descriptions[$iso] = $config->getPrefixedConfigItem($id . '_APM_desc_' . $iso, '');
        }

        $smarty
            ->assign('id', $id)
            ->assign('sortNr', $config->getPrefixedConfigItem($id . '_APM_sortNr', '0'))
            ->assign('pictureURL', $config->getPrefixedConfigItem($id . '_APM_pictureURL', ''))
            ->assign('paymentDesc', $descriptions)
            ->assign('availableLanguages', $allLang);

        $response->assignVar('result', 'success');
    }

    /**
     * @param IOResponse $response
     * @param array      $data
     * @return void
     */
    private function listActionAPMSettingsPost(IOResponse $response, array $data): void
    {
        $id       = Text::filterXSS($data['id']);
        $config   = PPCHelper::getConfiguration($this->plugin);
        $allLang  = LanguageHelper::getAllLanguages(0, true, true);
        $settings = [
            $id . '_APM_pictureURL' => Text::filterXSS($data['pictureURL']),
            $id . '_APM_sortNr'     => (string)(int)Text::filterXSS($data['sortNr']),
        ];

        foreach ($allLang as $languageModel) {
            $iso                                 = \strtoupper($languageModel->getIso());
            $settings[$id . '_APM_desc_' . $iso] = Text::filterXSS($data['paymentDesc_' . $iso]);
        }

        $config->saveConfigItems($settings);
        $response->assignVar('result', 'success');
    }

    /**
     * @param string $action
     * @param string $id
     * @return IOResponse
     * @uses listActionAPMSettingsGet
     */
    public function handlelistActionGet(string $action, string $id): IOResponse
    {
        $response = new IOResponse();
        $smarty   = Shop::Smarty();
        $method   = 'listAction' . \ucfirst($action) . 'Get';
        if (\method_exists($this, $method)) {
            $this->$method($response, $smarty, $id);
            try {
                $content = $smarty->fetch(
                    $this->plugin->getPaths()->getAdminPath() . '/template/listAction' . $action . '.tpl'
                );
            } catch (Exception) {
                $content = '';
            }
            $response->assignDom('actionBody-' . $action, 'innerHTML', $content);
        }

        return $response;
    }

    /**
     * @param string $action
     * @param string $query
     * @return IOResponse
     * @uses listActionAPMSettingsPost
     */
    public function handlelistActionPost(string $action, string $query): IOResponse
    {
        $response = new IOResponse();
        $method   = 'listAction' . \ucfirst($action) . 'Post';
        \parse_str($query, $data);

        if (\method_exists($this, $method) && Form::validateToken($data['jtl_token'])) {
            $this->$method($response, $data);
        }

        return $response;
    }

    public function handleShowResetCredentials(): IOResponse
    {
        $response       = new IOResponse();
        $smarty         = Shop::Smarty();
        $config         = PPCHelper::getConfiguration($this->plugin);
        $backendUser    = BackendUserData::getByID(
            Shop::Container()->getAdminAccount()->getID(),
            Shop::Container()->getDB()
        );
        $resetSerial    = $config->getPrefixedConfigItem(
            'resetCredentialsSingleCode_' . $config->getConfigValues()->getWorkingMode()
        );
        $resetCodeAvail = $resetSerial !== null && (new CredentialCode($resetSerial))->isExpired() === false;
        $pluginPaths    = $this->plugin->getPaths();
        $menuItem       = first($this->plugin->getAdminMenu()->getItems(), static function (object $mnuItem) {
            return $mnuItem->cName === 'Zugangsdaten';
        });
        try {
            $content = $smarty
                ->assign('basePath', $pluginPaths->getAdminPath())
                ->assign('jtl_token', Form::getTokenInput())
                ->assign('kPlugin', $this->plugin->getID())
                ->assign('kPluginAdminMenu', $menuItem->id)
                ->assign('twoFAavail', $backendUser->use2FA())
                ->assign('resetCodeAvail', $resetCodeAvail)
                ->assign('alertList', Shop::Container()->getAlertService())
                ->fetch(
                    $pluginPaths->getAdminPath() . '/template/credentials-reset.tpl'
                );
        } catch (Exception) {
            $content = '';
        }

        return $response->assignDom('disconnectPaypal-modalBody', 'innerHTML', $content);
    }

    /**
     * @throws AuthorizationException
     */
    public function handleShowResetCredentialsMail(): IOResponse
    {
        $resetController = new ResetCredentialsController($this->plugin, $this->db);
        $resetController->runSubTask('sendResetMail');

        return $this->handleShowResetCredentials();
    }
}
