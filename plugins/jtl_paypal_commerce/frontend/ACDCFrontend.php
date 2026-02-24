<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use Exception;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Customer\Customer;
use JTL\Helpers\Request;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Transaction;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Settings;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;

/**
 * Class ACDCFrontend
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class ACDCFrontend extends AbstractPaymentFrontend
{
    /**
     * @inheritDoc
     */
    public function renderProductDetailsPage(
        Customer $customer,
        Cart $cart,
        Address $shippingAddr,
        ?Artikel $product
    ): void {
        // no action at product details page
    }

    /**
     * @inheritDoc
     */
    public function renderCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        // no action at product details page
    }

    public function renderMiniCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        // no action at mini cart
    }

    /**
     * @inheritDoc
     */
    public function renderAddressPage(Customer $customer, Cart $cart): void
    {
        // no action at address page
    }

    /**
     * @inheritDoc
     */
    public function renderShippingPage(Customer $customer, Cart $cart): void
    {
        if (!$this->paymentMethod->isValid($customer, $cart)) {
            return;
        }

        /** @var Configuration $config */
        Transaction::instance()->clearAllTransactions();
        $acdcMethod = $this->paymentMethod->getMethod();
        $config     = PPCHelper::getConfiguration($this->plugin);
        $sca        = $config->getPrefixedConfigItem(
            Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY . '_activate3DSecure',
            'Y'
        ) !== 'Y' ? 'N' : $config->getPrefixedConfigItem(
            Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY . '_mode3DSecure',
            'SCA_WHEN_REQUIRED'
        );
        try {
            $vaultingHelper    = new VaultingHelper($config);
            $localization      = $this->plugin->getLocalization();
            $hostedFieldsTrans = [
                'acdc_card_header'                => $localization->getTranslation('acdc_card_header'),
                'acdc_card_number'                => $localization->getTranslation('acdc_card_number'),
                'acdc_card_date'                  => $localization->getTranslation('acdc_card_date'),
                'acdc_card_security_code'         => $localization->getTranslation('acdc_card_security_code'),
                'acdc_card_holder_name'           => $localization->getTranslation('acdc_card_holder_name'),
                'acdc_card_holder_equals_billing' => $localization->getTranslation('acdc_card_holder_equals_billing'),
                'acdc_card_holder_adress'         => $localization->getTranslation('acdc_card_holder_adress'),
            ];
            \pq('#' . $acdcMethod->getModuleID())
                ->append($this->smarty
                    ->assign('acdcModuleId', $acdcMethod->getModuleID())
                    ->assign('acdcPaymentId', $acdcMethod->getMethodID())
                    ->assign('acdcBNCode', MerchantCredentials::BNCODE_ACDC)
                    ->assign('acdcSCAMode', $sca)
                    ->assign('acdcMethodName', $this->paymentMethod->getLocalizedPaymentName())
                    ->assign('acdcGeneralError', \sprintf(
                        FrontendHandler::getBackendTranslation('Die Zahlungsmethode %s ist nicht verfügbar.'),
                        $this->paymentMethod->getLocalizedPaymentName()
                    ))
                    ->assign('acdc3DSError', $localization->getTranslation('acdc_3dserror_occured'))
                    ->assign('msg_acdc_invalid_input', $localization->getTranslation('acdc_invalid_input'))
                    ->assign('msg_acdc_potentially_valid', $localization->getTranslation('acdc_potentially_valid'))
                    ->assign('customer', $customer)
                    ->assign('acdcImagePath', $this->plugin->getPaths()->getFrontendURL() . 'img')
                    ->assign('hostedFieldsTranslation', $hostedFieldsTrans)
                    ->assign('fundingSource', PaymentSourceBuilder::FUNDING_CARD)
                    ->assign('vaulting_enabled', $this->paymentMethod->getCache('ppc_vaulting_enable') === 'Y')
                    ->assign('label_vaulting_enable', $localization->getTranslation(
                        'jtl_paypal_commerce_vaulting_enable_description'
                    ))
                    ->assign(
                        'acdcShowVaultingEnable',
                        $vaultingHelper->isVaultingEnabled(
                            $this->paymentMethod->getDefaultFundingSource(),
                            $customer->getID()
                        ) && !$vaultingHelper->isVaultingActive($customer->getID(), $this->paymentMethod)
                    )
                    ->fetch($acdcMethod->getAdditionalTemplate()));
        } catch (Exception) {
            $logger = Shop::Container()->getLogService();
            $logger->error('phpquery rendering failed: ACDCFrontend::renderShippingPage()');

            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function renderConfirmationPage(int $paymentId, Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        if ($this->paymentMethod->getMethod()->getMethodID() !== $paymentId) {
            return;
        }

        $vaultingChecked = Request::postVar('ppc_vaulting_enable', []);
        $vaultingHelper  = new VaultingHelper(PPCHelper::getConfiguration($this->plugin));
        $vaultingHelper->enableVaulting(
            PaymentSourceBuilder::FUNDING_CARD,
            $this->paymentMethod,
            (int)($vaultingChecked[PaymentSourceBuilder::FUNDING_CARD] ?? 0) > 0
        );

        $ppcOrderId    = $this->paymentMethod->createPPOrder(
            $customer,
            $cart,
            PaymentSourceBuilder::FUNDING_CARD,
            ExperienceContext::SHIPPING_PROVIDED,
            ExperienceContext::USER_ACTION_CONTINUE,
            $this->paymentMethod->getBNCode()
        );
        $ppOrder       = $this->paymentMethod->getPPOrder($ppcOrderId);
        $paymentSource = $ppOrder === null ? null : $ppOrder->getPaymentSource(PaymentSourceBuilder::FUNDING_CARD);
        if ($ppOrder === null || $paymentSource === null || $paymentSource->getType() === '') {
            Helper::redirectAndExit($this->paymentMethod->getPaymentCancelURL($ppOrder));
            exit();
        }
    }

    /**
     * @inheritDoc
     */
    public function renderFinishPage(Order $ppOrder, bool $payAgainProcess = false): void
    {
        // no action at finish page
    }
}
