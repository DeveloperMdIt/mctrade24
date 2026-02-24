<?php

declare(strict_types=1);

namespace Plugin\jtl_dhlwunschpaket;

use JTL\Alert\Alert;
use JTL\Backend\Notification;
use JTL\Backend\NotificationEntry;
use JTL\Catalog\Currency;
use JTL\Events\Dispatcher;
use JTL\Helpers\Request;
use JTL\IO\IO;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use Plugin\jtl_dhlwunschpaket\classes\JtlPack;

/**
 * Class Bootstrap
 * @package Plugin\jtl_dhlwunschpaket
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);
        $additional = Shop::getSettingValue(\CONF_KUNDEN, 'lieferadresse_abfragen_adresszusatz');
        if (
            $additional === 'N'
            && !Shop::isFrontend()
            && ($this->getPlugin()->getConfig()->getValue('jtl_pack_packstation_active') === 'Y'
                || $this->getPlugin()->getConfig()->getValue('jtl_pack_postfiliale_active') === 'Y'
            )
        ) {
            Notification::getInstance($this->getDB())->add(
                NotificationEntry::TYPE_WARNING,
                'DHL Wunschzustellung',
                \__('Please enable option "Prompt address 2" in the shipping address options')
            );
        }

        $jtlPack = new JtlPack($this->getPlugin(), $this->getDB());

        $dispatcher->listen('shop.hook.' . \HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE, function () use ($jtlPack) {
            $this->hook6($jtlPack);
        });

        $dispatcher->listen(
            'shop.hook.' . \HOOK_IO_HANDLE_REQUEST,
            static function ($args) use ($jtlPack) {
                /** @var IO $io */
                $io = $args['io'];
                $io->register('getAvailableDeliverySpots', [$jtlPack, 'getAvailableDeliverySpots'])
                    ->register('setJtlPackLocation', [$jtlPack, 'setJtlPackLocation'])
                    ->register('setJtlDeliveryWish', [$jtlPack, 'setJtlDeliveryWish']);
            }
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE_PLAUSI,
            static function () {
                if (Request::postInt('jtlPack') !== null) {
                    if (Request::postInt('jtlPack') >= -1) {
                        unset($_SESSION['jtlPack']);
                    } else {
                        $_SESSION['jtlPack'] = Request::postInt('jtlPack');
                    }
                } else {
                    unset($_SESSION['jtlPack']);
                }
            }
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_RECHNUNGLIEFERADRESSE,
            static function () {
                unset($_SESSION['jtlPack']);
            }
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_BESTELLVORGANG_PAGE_STEPVERSAND,
            function () use ($jtlPack) {
                $jtlPack->checkDeliveryAddress();
                $jtlPack->filterShippingMethods();
                $bServicesActive = $jtlPack->dhlServicesActive((int)($_SESSION['AktiveVersandart'] ?? 0));

                Shop::Smarty()->assign('bServicesActive', $bServicesActive);
                if ($bServicesActive) {
                    Shop::Smarty()->assign(
                        'availableDhlServices',
                        $jtlPack->getAvailableDhlServices($_SESSION['Lieferadresse']->cPLZ)
                    );
                }
            }
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG,
            function () use ($jtlPack) {
                $jtlPack->checkDeliveryAddress();
                $jtlPack->filterShippingMethods();

                $currency           = Currency::fromISO(Shop::Lang()->gibISO());
                $additionalCostsVal = $this->getPlugin()->getConfig()->getValue('jtl_pack_wunschtag_costs');
                $additionalCostsVal = \number_format(
                    $additionalCostsVal * $currency->getConversionFactor(),
                    2,
                    $currency->getDecimalSeparator(),
                    $currency->getThousandsSeparator()
                ) . ' ' . $currency->getHtmlEntity();

                $additionalCostsAdvice = \str_replace(
                    '###VERSANDKOSTEN_WUNSCHTAG###',
                    $additionalCostsVal,
                    $this->getPlugin()->getLocalization()->getTranslation('jtl_pack_dhl_wunschtag_hinweis')
                );

                Shop::Smarty()->assign('additionalCostsAdvice', $additionalCostsAdvice);
            }
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_LETZTERINCLUDE_INC,
            function () use ($jtlPack, $dispatcher) {
                $smarty = Shop::Smarty();
                if ($smarty->getTemplateVars('jtlPackPlugin') === null) {
                    $smarty->assign('jtlPackPlugin', $this->getPlugin());
                }
                $smarty->assign('jtlPackFormTranslations', $jtlPack->getFormTranslations());
                $bIsNova = (bool)$smarty->getTemplateVars('isNova');
                if ($bIsNova === true || Shop::getPageType() !== \PAGE_BESTELLVORGANG) {
                    return;
                }
                $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER, function (array $args) use ($jtlPack) {
                    $smarty         = $args['smarty'];
                    $servicesActive = $jtlPack->dhlServicesActive((int)$_SESSION['AktiveVersandart']);
                    $smarty->assign('bServicesActive', $servicesActive);
                    if ($servicesActive) {
                        $smarty->assign(
                            'availableDhlServices',
                            $jtlPack->getAvailableDhlServices($_SESSION['Lieferadresse']->cPLZ)
                        );
                    }
                    \pq('#checkout-shipping-payment')->append(
                        $smarty->fetch(
                            $this->getPlugin()->getPaths()->getFrontendPath() . '/template/evo/dhl_services.tpl'
                        )
                    );
                });
            }
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE,
            static function ($args) use ($jtlPack) {
                $jtlPack->setOrderAttributes($args['oBestellung']);
            }
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_VORHANDENELIEFERADRESSE,
            [$jtlPack, 'checkDeliveryAddress']
        );

        $dispatcher->listen(
            'shop.hook.' . \HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG,
            function () use ($jtlPack) {
                if (
                    isset($_SESSION['wunschtag_selected']) && $_SESSION['wunschtag_selected'] !== '0'
                    && $jtlPack->checkDeliveryDate((string)$_SESSION['wunschtag_selected']) === false
                ) {
                    unset($_SESSION['wunschtag_selected']);
                    $redirectUrl = Shop::Container()->getLinkService()->getStaticRoute(
                        'bestellvorgang.php'
                    ) . '?editVersandart=1&deliveryday_invalid';
                    \header('Location: ' . $redirectUrl);
                    exit;
                }

                $methodName      = $_SESSION['Versandart']->angezeigterName[$_SESSION['cISOSprache']]
                    ?? $_SESSION['Versandart']->cName;
                $shippingMethods = $jtlPack->filterShippingMethods([$_SESSION['Versandart']]);
                if (\count($shippingMethods) === 0) {
                    Shop::Container()->getAlertService()->addAlert(
                        Alert::TYPE_ERROR,
                        \sprintf(
                            $this->getPlugin()->getLocalization()->getTranslation('jtl_pack_invalid_delivery_method'),
                            $methodName
                        ),
                        'jtl_pack_invalid_delivery_method',
                        ['saveInSession' => true]
                    );
                    \header(
                        'Location: '
                        . Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php')
                        . '?editVersandart=1'
                    );
                    exit;
                }
                $jtlPack->setAdditionalCosts();
            }
        );
    }

    /**
     * @param JtlPack $jtlPack
     */
    private function hook6(JtlPack $jtlPack): void
    {
        $config              = $this->getPlugin()->getConfig();
        $packstationUsable   = $jtlPack->isShippingUsable($config->getValue('jtl_pack_shippingmethods_packstation'));
        $postfilialeUsable   = $jtlPack->isShippingUsable($config->getValue('jtl_pack_shippingmethods_filiale'));
        $wunschnachbarUsable = $jtlPack->isShippingUsable($config->getValue('jtl_pack_shippingmethods_neighbour'));

        Shop::Smarty()->assign('jtlPackPlugin', $jtlPack->getPlugin())
            ->assign('packstationUsable', $packstationUsable)
            ->assign('postfilialeUsable', $postfilialeUsable)
            ->assign('wunschnachbarUsable', $wunschnachbarUsable)
            ->assign('jtlPack', (int)($_SESSION['jtlPack'] ?? 0));
    }
}
