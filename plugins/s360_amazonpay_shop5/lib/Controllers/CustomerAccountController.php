<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use JTL\Campaign;
use JTL\CheckBox;
use JTL\Checkout\Kupon;
use JTL\Customer\AccountController;
use JTL\Customer\Customer;
use JTL\Customer\CustomerAttributes;
use JTL\Helpers\Form;
use JTL\Customer\Registration\Form as RegistrationForm; // Class exists only in 5.2.0+
use JTL\Helpers\Tax;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;

/**
 * Class CustomerAccountController
 *
 * Controls anything related to account actions.
 *
 * - Mapping
 * - Removing mappings
 * - Logins
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class CustomerAccountController {

    use JtlLoggerTrait;

    private $database;

    public function __construct() {
        $this->database = Database::getInstance();
    }

    /**
     * When an account gets deleted, we also delete all account mappings.
     * @param int $customerId
     */
    public function handleAccountDeletion(int $customerId): void {
        $this->database->deleteAccountMappingForJtlCustomerId($customerId);
        // Delete subscriptions for this customer!
        $subscriptionController = new SubscriptionController(Plugin::getInstance());
        $subscriptionController->cancelAllSubscriptionsForCustomer($customerId, Subscription::REASON_ACCOUNT_DELETED);
    }

    /**
     * Creates a customer in the shop with the given data.
     *
     * @param Customer $customer
     * @param $data
     * @param bool $isFullCustomer
     * @return Customer|null that was created
     */
    public function createCustomerAccount(Customer $customer, $data, bool $isFullCustomer): ?Customer {

        $oCheckBox = new CheckBox();
        $conf = Shop::getSettings([CONF_GLOBAL, CONF_KUNDENWERBENKUNDEN]);
        $cart = Frontend::getCart();
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);

        if(Compatibility::isShopAtLeast52()) {
            /** @noinspection PhpUndefinedClassInspection - Class exists only in 5.2.0+*/
            $customerAttributes = (new RegistrationForm())->getCustomerAttributes($data);
        } else {
            /** @noinspection PhpDeprecationInspection */
            $customerAttributes = getKundenattribute($data);
        }

        $customerGroupId = $customer->kKundengruppe;
        
        // execute checkbox functionality and log checkbox info
        $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_REGISTRIERUNG, $customerGroupId, true, $data, array('oKunde' => $customer));
        $oCheckBox->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $customerGroupId, $data, true);

        $customer->kKundengruppe = $customerGroupId; // overwrite customer group id in case it was changed
        $customer->kSprache = Shop::getLanguage();
        $customer->cAbgeholt = 'N';
        $customer->cSperre = 'N';
        $customer->cAktiv = 'Y'; // may be overriden for full customers
        $customer->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($customer->cLand);
        $customer->nRegistriert = 0; // may be overridden for full customers
        if ($isFullCustomer) {
            // only for new registered customer, a guest is saved to DB AFTER checkout when the order is saved to the database
            $customer->cAktiv = $conf['global']['global_kundenkonto_aktiv'] === 'A' ? 'N' : 'Y';
            $cPasswortKlartext = $customer->cPasswort;
            $customer->cPasswort = Shop::Container()->getPasswordService()->hash($cPasswortKlartext);
            $customer->dErstellt = 'NOW()';
            $customer->nRegistriert = 1;
            $cLand = $customer->cLand;
            $customer->cPasswortKlartext = $cPasswortKlartext;
            $obj = new \stdClass();
            $obj->tkunde = $customer;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail = new Mail();
            $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj));

            $customer->cLand = $cLand;
            unset($customer->cPasswortKlartext, $customer->Anrede);

            $customer->kKunde = $customer->insertInDB();
            // Kampagne
            if (isset($_SESSION['Kampagnenbesucher'])) {
                Campaign::setCampaignAction(KAMPAGNE_DEF_ANMELDUNG, $customer->kKunde, 1.0); // Anmeldung
            }
            // Insert Kundenattribute, be sure to add the customer id first
            /** @var CustomerAttributes $customerAttributes */
            $customerAttributes->setCustomerID($customer->kKunde);
            $customerAttributes->save();

            // If global customer account activation is disabled - the calling method has to check if this creation also set the user in the session.
            if ($conf['global']['global_kundenkonto_aktiv'] !== 'A') {
                $_SESSION['Kunde'] = new Customer($customer->kKunde);
                $_SESSION['Kunde']->getCustomerAttributes()->load($customer->kKunde);
            }
        } else {
            // guest login, the customer is not created in the database, additional data is only set on completion of the order
            $_SESSION['Kunde'] = $customer;

            // Save customer attributes for later - this will be read by bestellungInDB for guest orders
            Frontend::set('customerAttributes', $customerAttributes);
        }

        // Perform coupon check
        $this->checkCoupons($this->getCoupons());

        // Refresh tax rates and the basket.
        if (isset($cart->kWarenkorb) && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0) {
            Tax::setTaxRates();
            $cart->gibGesamtsummeWarenLocalized();
        }

        return $customer;
    }

    /**
     * Performs a login for an existing customer for the given JTL Customer id.
     *
     * Attention: Do not try to login customers that do not have a verified account mapping!
     *
     * @param $jtlCustomerId
     * @return void
     */
    public function loginCustomer($jtlCustomerId): void {

        // NOTE: Some of the following calls will fail with an error if bestellvorgang_inc.php is not included here (AccountController does not require it by itself, although it indirectly needs it to check the coupons)
        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

        $this->debugLog('Trying to log in customer with id: "' . $jtlCustomerId . '"', 'CustomerAccountController');

        $customer = new Customer((int) $jtlCustomerId);
        if (empty($customer->getID()) || $customer->getID() <= 0) {
            $this->debugLog('Customer with id: "' . $jtlCustomerId . '" was not found. Cancelling login.', 'CustomerAccountController');
            return;
        }
        if ($customer->nRegistriert !== 1) {
            $this->debugLog('Customer with id: "' . $jtlCustomerId . '" is a guest account and cannot be logged in. Cancelling login.', 'CustomerAccountController');
            return;
        }

        /**
         * The alternative/intuitive method $customer->isLoggedIn() does NOT do what you expect - it currently simply returns true iff kKunde > 0 - this is completely useless to us.
         */
        if ($this->isCustomerLoggedIn((int) $jtlCustomerId)) {
            // we do not need to re-login a user that is already logged in.
            $this->debugLog('Customer with id: "' . $jtlCustomerId . '" is already logged in, skipping.', 'CustomerAccountController');
            return;
        }


        // We login the customer by using the JTL account controller
        // Note: During initCustomer the Account Controller tries to merge baskets, and while doing that, it checks for a given CSRF Token
        // The CSRF Token is not available here as everything here is handled during a redirect from Amazon Pay - thus we have to "fake" the CSRF token for this case.
        if(empty($_POST['jtl_token'])) {
            $this->debugLog('Mapping CSRF token from Session into Post array to allow for merging of baskets.', 'CustomerAccountController');
            $_POST['jtl_token'] = $_SESSION['jtl_token'];
        }
        // Now initialize JTL's AccountController and log in the user.
        $this->debugLog('Customer with id: "' . $jtlCustomerId . '" was loaded from the database and seems to be in order; initializing JTL core AccountController.', 'CustomerAccountController');
        $accountController = new AccountController(Shop::Container()->getDB(), Shop::Container()->getAlertService(), Shop::Container()->getLinkService(), Shop::Smarty());
        $this->debugLog('Handing over login for customer with id: "' . $jtlCustomerId . '" to JTL core AccountController.', 'CustomerAccountController');
        $accountController->initCustomer($customer);
        return;
    }


    /**
     * @param $customerId - to check if a specific customer is logged in, give his id here, else leave it as null and the method just checks if the current customer is logged in
     * @return bool
     */
    public function isCustomerLoggedIn(int $customerId = null): bool {
        if (null === $customerId) {
            return isset($_SESSION['Kunde'], $_SESSION['Kunde']->kKunde) && (int)$_SESSION['Kunde']->kKunde > 0;
        }

        return isset($_SESSION['Kunde'], $_SESSION['Kunde']->kKunde) && (int)$_SESSION['Kunde']->kKunde > 0 && (int)$_SESSION['Kunde']->kKunde === $customerId;
    }

    /**
     * Checks if a customer exists in the session.
     */
    public function isCustomerPresent(): bool {
        return isset($_SESSION['Kunde']);
    }

    /**
     * Checks if the customer in the session is a guest.
     * Note: This method also returns FALSE if no customer is present in the Session, at all.
     *
     * @return bool
     */
    public function isCustomerGuest(): bool {
        return isset($_SESSION['Kunde']) && (int)$_SESSION['Kunde']->nRegistriert === 0;
    }

    /**
     * Just returns the customer id of the current customer in the session.
     * @return int
     */
    public function getCurrentCustomerId(): int {
        return (int)$_SESSION['Kunde']->kKunde;
    }

    /**
     * 1:1 Copy of a private method in JTLs AccountController.
     * TODOLATER: Maybe use the original method if it becomes public?
     * @return array
     */
    private function getCoupons(): array {
        $coupons = [];
        $coupons[] = !empty($_SESSION['VersandKupon']) ? $_SESSION['VersandKupon'] : null;
        $coupons[] = !empty($_SESSION['oVersandfreiKupon']) ? $_SESSION['oVersandfreiKupon'] : null;
        $coupons[] = !empty($_SESSION['NeukundenKupon']) ? $_SESSION['NeukundenKupon'] : null;
        $coupons[] = !empty($_SESSION['Kupon']) ? $_SESSION['Kupon'] : null;

        return $coupons;
    }

    /**
     * 1:1 Copy of a private method in JTLs AccountController.
     * TODOLATER: Maybe use the original method if it becomes public?
     * @param array $coupons
     */
    private function checkCoupons(array $coupons): void {
        // NOTE: The following will fail with an error if bestellvorgang_inc.php is not included here (Kupon does not require it by itself and we need it for angabenKorrekt)
        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
        foreach ($coupons as $coupon) {
            if (empty($coupon)) {
                continue;
            }
            $error = Kupon::checkCoupon($coupon);
            if(Compatibility::isShopAtLeast52()) {
                $returnCode = Form::hasNoMissingData($error);
            } else {
                /** @noinspection PhpDeprecationInspection */
                $returnCode = \angabenKorrekt($error);
            }
            \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                'error' => &$error,
                'nReturnValue' => &$returnCode
            ]);
            if ($returnCode) {
                if (isset($coupon->kKupon) && $coupon->kKupon > 0 && $coupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                    Kupon::acceptCoupon($coupon);
                    \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                } elseif (!empty($coupon->kKupon) && $coupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                    // Versandfrei Kupon
                    $_SESSION['oVersandfreiKupon'] = $coupon;
                    Shop::Smarty()->assign(
                        'cVersandfreiKuponLieferlaender_arr',
                        \explode(';', $coupon->cLieferlaender)
                    );
                }
            } else {
                Frontend::getCart()->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
                Kupon::mapCouponErrorMessage($error['ungueltig']);
            }
        }
    }

}
