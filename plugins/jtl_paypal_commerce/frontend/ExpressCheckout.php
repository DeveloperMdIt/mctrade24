<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Customer\Registration\Form as RegistrationForm;
use JTL\Helpers\Form;
use JTL\Plugin\Data\PaymentMethod;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\LegacyHelper;
use Plugin\jtl_paypal_commerce\paymentmethod\PaymentmethodNotFoundException;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\OrderNotFoundException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\Payer;
use Plugin\jtl_paypal_commerce\PPC\Order\Shipping;
use Plugin\jtl_paypal_commerce\PPC\Order\ShippingOption;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Settings;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;

use function Functional\first;

/**
 * Class ExpressCheckout
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class ExpressCheckout
{
    public const PP_INVOICE_ADDRESS_EXTEND_WITH_ASKING    = 'E';
    public const PP_INVOICE_ADDRESS_EXTEND_WITHOUT_ASKING = 'Y';
    public const PP_INVOICE_ADDRESS_ASK_CUSTOMER          = 'N';
    public const PP_INVOICE_ADDRESS_USE_SHIPPING_ADDRESS  = 'O';

    /**
     * @param string[] $address
     * @return object
     */
    private static function extractStreet(array $address): object
    {
        $street = \array_shift($address);
        $result = (object)[
            'name'    => '',
            'number'  => '',
            'name2'   => \array_shift($address) ?? '',
        ];
        if ($street !== null) {
            $re     = "/^(\\d*[\\wäöüß&; '\\-.]+)[,\\s]+(\\d+)\\s*([\\wäöüß&;\\-\\/]*)$/ui";
            $number = '';
            if (\preg_match($re, $street, $matches)) {
                $offset = \mb_strlen($matches[1]);
                $number = \mb_substr($street, $offset);
                $street = \mb_substr($street, 0, $offset);
            }

            $result->name   = \trim($street, '-:, ');
            $result->number = \trim($number, '-:, ');
        }

        return $result;
    }

    /**
     * @param string $name
     * @return object
     */
    private static function extractName(string $name): object
    {
        $parts = \explode(' ', $name, 2);
        if (\count($parts) === 1) {
            \array_unshift($parts, '');
        }

        return (object)[
            'givenName' => \trim($parts[0]),
            'surName'   => \trim($parts[1]),
        ];
    }

    /**
     * @param Address $address
     * @param array   $data
     */
    private static function mapAddressData(Address $address, array &$data): void
    {
        $street               = self::extractStreet($address->getAddress());
        $data['strasse']      = $street->name;
        $data['hausnummer']   = $street->number;
        $data['adresszusatz'] = $street->name2;
        $data['bundesland']   = $address->getState();
        $data['plz']          = $address->getPostalCode();
        $data['ort']          = $address->getCity();
        $data['land']         = $address->getCountryCode();
    }

    /**
     * @param Payer $payer
     * @return string[]
     */
    public static function mapCustomerPostData(Payer $payer): array
    {
        $data             = [];
        $address          = $payer->getAddress();
        $data['anrede']   = '';
        $data['vorname']  = $payer->getGivenName();
        $data['nachname'] = $payer->getSurname();
        $data['tel']      = $payer->getPhone()->getNumber();
        $data['email']    = $payer->getEmail();
        if ($address !== null) {
            self::mapAddressData($address, $data);
        }

        return $data;
    }

    /**
     * @param Shipping $shipping
     * @return array
     */
    public static function mapShippingPostData(Shipping $shipping): array
    {
        // ToDo: Handle phone number and email
        $data             = [];
        $address          = $shipping->getAddress();
        $name             = self::extractName($shipping->getName());
        $conf             = Shop::getSettings([\CONF_KUNDEN]);
        $data['anrede']   = '';
        $data['vorname']  = $name->givenName;
        $data['nachname'] = $name->surName;
        $data['email']    = '';
        foreach (['tel', 'mobil', 'fax'] as $telType) {
            if ($conf['kunden']['lieferadresse_abfragen_' . $telType] !== 'N') {
                $data[$telType] = '';
            }
        }
        self::mapAddressData($address, $data);

        return $data;
    }

    /**
     * @param array $customerPost
     * @return array
     * @noinspection DuplicatedCode
     */
    private function checkoutCustomer(array $customerPost): array
    {
        $registrationForm        = new RegistrationForm();
        $result                  = $registrationForm->checkKundenFormularArray($customerPost, false);
        $customer                = $registrationForm->getCustomerData($customerPost, false);
        $customer->kKundengruppe = Frontend::getCustomerGroup()->getID();
        $customer->kSprache      = Shop::getLanguageID();
        $customer->cAbgeholt     = 'N';
        $customer->cAktiv        = 'Y';
        $customer->cSperre       = 'N';
        $customer->nRegistriert  = 1;
        $customer->dErstellt     = \date_format(\date_create(), 'Y-m-d');
        unset($result['captcha']); // ignore captcha validation

        if (empty($result)) {
            Frontend::set('Kunde', $customer);
        }

        return $result;
    }

    /**
     * @param array   $customerPost
     * @param array   $missingData
     * @param Address $address
     * @return array
     */
    private function applyMissingCustomer(array $customerPost, array $missingData, Address $address): array
    {
        $street = self::extractStreet($address->getAddress());
        if (isset($missingData['strasse'])) {
            $customerPost['strasse'] = $street->name;
        }
        if (isset($missingData['hausnummer'])) {
            $customerPost['hausnummer'] = $street->number;
        }
        if (isset($missingData['plz'])) {
            $customerPost['plz'] = $address->getPostalCode();
        }
        if (isset($missingData['ort'])) {
            $customerPost['ort'] = $address->getCity();
        }
        if (isset($missingData['land'])) {
            $customerPost['land'] = $address->getCountryCode();
        }

        return $customerPost;
    }

    private function applyShippingData(array $customerPost, Address $address, string $nameToShipTo): array
    {
        $street                       = self::extractStreet($address->getAddress());
        $name                         = self::extractName($nameToShipTo);
        $customerPost['vorname']      = $name->givenName;
        $customerPost['nachname']     = $name->surName;
        $customerPost['strasse']      = $street->name;
        $customerPost['hausnummer']   = $street->number;
        $customerPost['adresszusatz'] = $street->name2 ?? null;
        $customerPost['plz']          = $address->getPostalCode();
        $customerPost['ort']          = $address->getCity();
        $customerPost['land']         = $address->getCountryCode();

        return $customerPost;
    }

    /**
     * @param array $shippingPost
     * @return array
     */
    private function checkoutShipping(array $shippingPost): array
    {
        $result = (new RegistrationForm())->checkLieferFormularArray($shippingPost);
        $order  = Frontend::get('Bestellung') ?? new Bestellung();
        if (\count($result) === 0) {
            Frontend::set('Lieferadresse', Lieferadresse::createFromPost($shippingPost));
            $order->kLieferadresse = -1;
        }
        Frontend::set('Bestellung', $order);

        return $result;
    }

    /**
     * @param Address                $address
     * @param PayPalPaymentInterface $payMethod
     * @param ShippingOption|null    $option
     */
    private function checkoutShippingMethod(
        Address $address,
        PayPalPaymentInterface $payMethod,
        ?ShippingOption $option = null
    ): void {
        $customerGroupId = Frontend::getCustomer()->getGroupID() > 0
            ? Frontend::getCustomer()->getGroupID()
            : CustomerGroup::getDefaultGroupID();
        $cart            = Frontend::getCart();
        $shippingClasses = LegacyHelper::getShippingClasses($cart);
        $shippingMethods = LegacyHelper::getPossibleShippingMethods(
            $address->getCountryCode(),
            $address->getPostalCode(),
            $customerGroupId,
            $cart
        );
        $shippingMethod  = $option === null ? null : first(
            $shippingMethods,
            static function (\stdClass $shippingMethod) use ($payMethod, $option, $shippingClasses, $customerGroupId) {
                return (int)$shippingMethod->kVersandart === (int)$option->getId()
                    && $payMethod->isAssigned($shippingClasses, $customerGroupId, (int)$shippingMethod->kVersandart);
            }
        );
        if ($shippingMethod === null) {
            $shippingMethod = LegacyHelper::getCheapestShippingMethod(
                $shippingMethods,
                $payMethod->getMethod()->getMethodID(),
                $customerGroupId
            );
        }

        if ($shippingMethod !== null) {
            Frontend::set('Versandart', $shippingMethod);
            Frontend::set('AktiveVersandart', $shippingMethod->kVersandart);
        }
    }

    /**
     * @param PaymentMethod $payMethod
     * @return array
     */
    private function checkoutPayment(PaymentMethod $payMethod): array
    {
        Frontend::set('Zahlungsart', $payMethod);
        Frontend::set('AktiveZahlungsart', $payMethod->getMethodID());

        return [
            'Zahlungsart'     => $payMethod->getMethodID(),
            'zahlungsartwahl' => '1',
        ];
    }

    /**
     * @param string   $handleAddress
     * @param array    $customerPost
     * @param Shipping $shipping
     * @param array    $missingData
     * @return array
     */
    private function handleIncompleteCustomer(
        string $handleAddress,
        array $customerPost,
        Shipping $shipping,
        array $missingData
    ): array {
        switch ($handleAddress) {
            case self::PP_INVOICE_ADDRESS_ASK_CUSTOMER:
                break;
            case self::PP_INVOICE_ADDRESS_USE_SHIPPING_ADDRESS:
                $customerPost = $this->applyShippingData(
                    $customerPost,
                    $shipping->getAddress(),
                    $shipping->getName()
                );
                $missingData  = $this->checkoutCustomer($customerPost);
                break;
            case self::PP_INVOICE_ADDRESS_EXTEND_WITHOUT_ASKING:
            default:
                $customerPost = $this->applyMissingCustomer(
                    $customerPost,
                    $missingData,
                    $shipping->getAddress()
                );
                $missingData  = $this->checkoutCustomer($customerPost);
                break;
        }

        return [$customerPost, $missingData];
    }

    /**
     * @param array $missingData
     * @param array $customerPost
     * @param string $handleAddress
     * @return bool
     */
    private function isDataCheckByCustomerNotNecessary(
        array $missingData,
        array $customerPost,
        string $handleAddress
    ): bool {
        if (Form::hasNoMissingData($missingData) === 0) {
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart'], $_SESSION['Lieferadresse']);
            $_SESSION['Bestellung']->kLieferadresse = 0;
            $_SESSION['checkout.register']          = 1;
            $_SESSION['checkout.cPost_arr']         = \array_merge($customerPost, ['shipping_address' => 0]);
            if ($handleAddress === self::PP_INVOICE_ADDRESS_ASK_CUSTOMER || !empty($missingData)) {
                $_SESSION['checkout.fehlendeAngaben'] = $missingData;
            }

            return false;
        }

        return true;
    }

    /**
     * @param PayPalPaymentInterface $payMethod
     * @param Configuration          $config
     * @param Customer               $customer
     * @return bool
     * @throws PaymentmethodNotFoundException
     */
    public function ecsCheckout(PayPalPaymentInterface $payMethod, Configuration $config, Customer $customer): bool
    {
        try {
            if (($ppOrder = $payMethod->getPPOrder()) === null) {
                throw new OrderNotFoundException('No order found');
            }
            //get ECS Order
            $ecsOrder       = $payMethod->verifyPPOrder($ppOrder->getId());
            $payer          = $ecsOrder->getPayer();
            $shipping       = $ecsOrder->getPurchase()->getShipping();
            $shippingOption = $shipping?->getOption();
            $ppOrder->setStatus($ecsOrder->getStatus());
        } catch (OrderNotFoundException | PPCRequestException $e) {
            throw new PaymentmethodNotFoundException('Paymentmethod PayPalCommerce not found', $e->getCode(), $e);
        }

        $handleAddress = $config->getPrefixedConfigItem(
            Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY . '_handleInvoiceAddress',
            self::PP_INVOICE_ADDRESS_EXTEND_WITH_ASKING
        );

        $customerPost = [];
        $missingData  = [];
        if ($shipping !== null && $payer !== null && $customer->getID() === 0) {
            $customerPost = self::mapCustomerPostData($payer);
            $missingData  = $this->checkoutCustomer($customerPost);
            if ($payer->getAddress() !== null && \count($missingData) === 0) {
                $this->checkoutShippingMethod($payer->getAddress(), $payMethod, $shippingOption);
            } else {
                [$customerPost, $missingData] = $this->handleIncompleteCustomer(
                    $handleAddress,
                    $customerPost,
                    $shipping,
                    $missingData
                );
            }
        }

        if ($shipping !== null) {
            $shippingPost = self::mapShippingPostData($shipping);
            $missingData  = \array_merge($missingData, $this->checkoutShipping($shippingPost));
            $this->checkoutShippingMethod($shipping->getAddress(), $payMethod, $shippingOption);
        }
        $payMethod->setBNCode(MerchantCredentials::BNCODE_EXPRESS);
        $returnValue = $this->isDataCheckByCustomerNotNecessary(
            $missingData,
            $customerPost,
            $handleAddress
        );
        ControllerFactory::getCheckoutController()
                      ->checkStepPaymentMethodSelection($this->checkoutPayment($payMethod->getMethod()));

        return $returnValue;
    }

    public function applyVaultingAddress(
        VaultingHelper $vaultingHelper,
        Customer $customer,
        PayPalPaymentInterface $paymentMethod
    ): ?Address {
        $frontendAddress = Frontend::getDeliveryAddress();
        if (!LegacyHelper::isAddressEmpty($frontendAddress)) {
            return Address::createFromOrderAddress($frontendAddress);
        }

        $shippingAddress = $vaultingHelper->getShippingAddress($customer->getID(), $paymentMethod);
        if ($shippingAddress !== null) {
            $deliveryAddress            = new Lieferadresse();
            $deliveryAddress->cLand     =
            $deliveryAddress->cAnrede   = $customer->cAnrede ?? '';
            $deliveryAddress->cVorname  = $customer->cVorname ?? '';
            $deliveryAddress->cNachname = $customer->cNachname ?? '';
            $deliveryAddress->cTitel    = $customer->cTitel ?? '';
            $deliveryAddress->cMail     = $customer->cMail ?? '';

            $address                        = self::extractStreet($shippingAddress->getAddress());
            $deliveryAddress->cStrasse      = $address->name;
            $deliveryAddress->cHausnummer   = $address->number;
            $deliveryAddress->cAdressZusatz = $address->name2;
            $deliveryAddress->cPLZ          = $shippingAddress->getPostalCode();
            $deliveryAddress->cOrt          = $shippingAddress->getCity();
            $deliveryAddress->cBundesland   = $shippingAddress->getState() ?? '';
            $deliveryAddress->cLand         = $shippingAddress->getCountryCode();

            Frontend::setDeliveryAddress($deliveryAddress);
        }

        return $shippingAddress;
    }
}
