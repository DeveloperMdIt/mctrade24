<?php

declare(strict_types=1);

namespace Plugin\jtl_dhlwunschpaket\classes;

use Carbon\Carbon;
use GuzzleHttp\Client;
use JsonException;
use JTL\Cart\Cart;
use JTL\Catalog\Currency;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Versandart;
use JTL\DB\DbInterface;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\GeneralObject;
use JTL\IO\IOResponse;
use JTL\Plugin\Data\Config;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class JtlPack
 * @package Plugin\jtl_dhlwunschpaket\classes
 */
class JtlPack
{
    /**
     * @var PluginInterface
     */
    private PluginInterface $plugin;

    /**
     * @var bool
     */
    private bool $isDebug;

    /**
     * @var DbInterface $db
     */
    private DbInterface $db;

    /**
     * @var bool
     */
    private bool $useSandbox;

    public const DHLUSER = 'anRsX2RobHd1bnNjaHBha2V0XzE=';

    public const DHLPASS = 'T09VS0pSaUY0MjI5WkFBMWJBcHdLSUFqT0wyZmxG';

    private const API_KEY = 'IoGzaECJzYhFHEmRIPBgjwhdfeJceRZ9';

    /**
     * JtlPack constructor.
     * @param PluginInterface $plugin
     * @param DbInterface     $db
     */
    public function __construct(PluginInterface $plugin, DbInterface $db)
    {
        $this->setPlugin($plugin)
            ->setDb($db)
            ->setIsDebug($this->getPlugin()->getConfig()->getValue('jtl_pack_debug_syslog') === '1')
            ->setUseSandbox($this->getPlugin()->getConfig()->getValue('jtl_pack_sandbox') === 'Y');
    }

    /**
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @param PluginInterface $plugin
     * @return JtlPack
     */
    public function setPlugin(PluginInterface $plugin): JtlPack
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @param bool $useSandbox
     * @return JtlPack
     */
    public function setUseSandbox(bool $useSandbox): JtlPack
    {
        $this->useSandbox = $useSandbox;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->isDebug;
    }

    /**
     * @param bool $isDebug
     * @return JtlPack
     */
    public function setIsDebug(bool $isDebug): JtlPack
    {
        $this->isDebug = $isDebug;

        return $this;
    }

    /**
     * @return false|string
     */
    public function getFormTranslations()
    {
        $translations                            = [];
        $translations['default']['street']       = Shop::Lang()->getTranslation('street', 'account data');
        $translations['default']['streetnumber'] = Shop::Lang()->getTranslation('streetnumber', 'account data');
        $translations['default']['additional']   = Shop::Lang()->getTranslation('street2', 'account data');

        $translations['packstation']['street']       = 'Packstation';
        $translations['packstation']['streetnumber'] = $this->getPlugin()->getLocalization()->getTranslation(
            'ps_la_dhl_packnr'
        );
        $translations['packstation']['additional']   = $this->getPlugin()->getLocalization()->getTranslation(
            'ps_la_dhl_postnr'
        );

        $translations['postfiliale']['street']       = 'Postfiliale';
        $translations['postfiliale']['streetnumber'] = $this->getPlugin()->getLocalization()->getTranslation(
            'ps_la_dhl_filinr'
        );
        $translations['postfiliale']['additional']   = $translations['packstation']['additional'];

        return \json_encode($translations);
    }

    /**
     * @param $address
     * @return IOResponse
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \SmartyException
     */
    public function getAvailableDeliverySpots($address): IOResponse
    {
        $logger     = Shop::Container()->getLogService();
        $response   = new IOResponse();
        $locations  = [];
        $nFiliCount = 0;
        $nPackCount = 0;
        $path       = $this->getPlugin()->getPaths()->getFrontendPath();
        $tmpData    = \explode('|', $address);
        $smarty     = Shop::Smarty();
        $client     = new Client();
        $headers    = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'DHL-API-Key'  => self::API_KEY
        ];
        $url        = 'https://api.dhl.com/location-finder/v1/find-by-address';
        $queries    = [
            'countryCode=DE&postalCode=' . $tmpData[0] . '&serviceType=parcel%3Apick-up&limit=8',
            'countryCode=DE&postalCode=' . $tmpData[0] . '&serviceType=parcel%3Apick-up-registered&limit=8'
        ];
        foreach ($queries as $query) {
            $req = $client->request('GET', $url, [
                'headers' => $headers,
                'query'   => $query,
                'verify'  => true
            ]);
            if ($this->isDebug()) {
                $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . ' - Result: ' . (string)$req->getBody());
            }
            if ($req->getStatusCode() !== 200) {
                $smarty->assign('jtlPackPlugin', $this->getPlugin())
                    ->assign('nFiliCount', $nFiliCount)
                    ->assign('nPackCount', $nPackCount)
                    ->assign('locations', $locations)
                    ->assign('type', $tmpData[5]);

                $content = \file_exists($path . 'template/jtl_pack_location_lookup_results_custom.tpl')
                    ? $smarty->fetch($path . 'template/jtl_pack_location_lookup_results_custom.tpl')
                    : $smarty->fetch($path . 'template/jtl_pack_location_lookup_results.tpl');

                $response->assignVar('content', $content)
                    ->assignVar('error', true)
                    ->assignVar('status_code', $req->getStatusCode());
                break;
            }
            try {
                $body = \json_decode((string)$req->getBody(), null, 512, \JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $logger->error('Plugin ' . $this->getPlugin()->getPluginID() . ' - Error: ' . $e->getMessage());
                $body = (object)['locations' => []];
            }
            foreach ($body->locations as $location) {
                $element                 = new stdClass();
                $element->type           = $location->location->type;
                $element->depotServiceNo = '';
                if ($location->location->type === 'locker') {
                    $element->keyWord  = 'Packstation';
                    $element->shopType = 'Packstation';
                    $nPackCount++;
                } else {
                    $element->depotServiceNo = $location->location->keywordId;
                    $element->keyWord        = 'Postfiliale';
                    $element->shopType       = $location->location->type === 'servicepoint'
                        ? 'retailoutlet'
                        : 'dhlpaketshop';
                    $nFiliCount++;
                }
                $element->shopName              = $location->name;
                $element->packstationId         = $location->location->keywordId;
                $element->street                = $location->place->address->streetAddress;
                $element->zipCode               = $location->place->address->postalCode;
                $element->district              = $location->place->address->addressLocality;
                $element->geoPosition           = new stdClass();
                $element->geoPosition->distance = (int)$location->distance;

                $locations[] = $element;
            }
        }
        $smarty->assign('jtlPackPlugin', $this->getPlugin())
            ->assign('nFiliCount', $nFiliCount)
            ->assign('nPackCount', $nPackCount)
            ->assign('locations', $locations)
            ->assign('type', $tmpData[5]);
        $content = \file_exists($path . 'template/jtl_pack_location_lookup_results_custom.tpl')
            ? $smarty->fetch($path . 'template/jtl_pack_location_lookup_results_custom.tpl')
            : $smarty->fetch($path . 'template/jtl_pack_location_lookup_results.tpl');

        $response->assignVar('locations', $locations)
            ->assignVar('nFiliCount', $nFiliCount)
            ->assignVar('nPackCount', $nPackCount)
            ->assignVar('content', $content)
            ->assignVar('error', false)
            ->assignVar('status_code', 200);

        return $response;
    }

    /**
     * @param      $url
     * @param null $postdata
     * @return array
     */
    private function callDhlApi($url, $postdata = null): array
    {
        $logger    = Shop::Container()->getLogService();
        $apiResult = [];
        $ch        = \curl_init();

        \curl_setopt($ch, \CURLOPT_URL, $url);
        if ($postdata !== null) {
            \curl_setopt($ch, \CURLOPT_POST, true);
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $postdata); // the SOAP request
        }
        \curl_setopt($ch, \CURLOPT_TIMEOUT, 10000);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, \CURLOPT_HTTPAUTH, \CURLAUTH_ANY);

        if ($this->useSandbox === true) {
            \curl_setopt(
                $ch,
                \CURLOPT_USERPWD,
                $this->getPlugin()->getConfig()->getValue('jtl_pack_sandboxuser') .
                ':' .
                $this->getPlugin()->getConfig()->getValue('jtl_pack_sandboxpassword')
            );
        } else {
            \curl_setopt($ch, \CURLOPT_USERPWD, \base64_decode(self::DHLUSER) . ':' . \base64_decode(self::DHLPASS));
        }

        \curl_setopt(
            $ch,
            \CURLOPT_HTTPHEADER,
            ['X-EKP: ' . $this->getPlugin()->getConfig()->getValue('jtl_pack_dhl_ekp')]
        );

        $result = \curl_exec($ch);

        if ($this->isDebug()) {
            $logger->debug(
                'Plugin ' . $this->getPlugin()->getPluginID() . ' - Result in callDhlApi-Method: ' . \print_r(
                    $apiResult,
                    true
                )
            );
        }
        $status_code = \curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        \curl_close($ch);

        $apiResult['result']      = $result;
        $apiResult['status_code'] = (string)$status_code;

        return $apiResult;
    }

    /**
     * @param int $shippingMethodID
     * @return bool
     */
    public function dhlServicesActive(int $shippingMethodID): bool
    {
        $config = $this->getPlugin()->getConfig();
        if (
            $config->getValue('jtl_pack_wunschtag_active') === 'N'
            && $config->getValue('jtl_pack_wunschort_active') === 'N'
        ) {
            return false;
        }

        $allowedShippingMethods = $config->getValue('jtl_pack_shippingmethods_services');
        if (!\is_array($allowedShippingMethods) || (int)($_SESSION['jtlPack'] ?? -2) >= -1) {
            return false;
        }

        $isActive =
            \in_array(
                $shippingMethodID,
                \array_map('\intval', $allowedShippingMethods),
                true
            )
            && (!isset($_SESSION['jtlPack']) || (int)$_SESSION['jtlPack'] > -2);

        if ($isActive === false) {
            unset($_SESSION['wunschtag_selected'], $_SESSION['wunschlocation']);
        }

        return $isActive;
    }

    private function getItemManipulationTime(Config $config): int
    {
        $result      = 0;
        $workingtime = $config->getValue('jtl_pack_workingtime');
        if (\is_numeric($workingtime)) {
            $result = (int)$workingtime;
        }
        if ($config->getValue('jtl_pack_checkdeliverydays') !== 'Y') {
            return $result;
        }
        // Workaround for wrong shipping method set - SHOP-8030
        $_SESSION['Versandart'] ??= new Versandart((int)$_SESSION['AktiveVersandart']);
        if (
            $_SESSION['Versandart'] instanceof Versandart === false
            || $_SESSION['Warenkorb'] instanceof Cart === false
        ) {
            return $result;
        }
        $deliveryDays = $_SESSION['Warenkorb']->getLongestMinMaxDelivery();
        if ($deliveryDays === null) {
            return $result;
        }
        // Fix for SHOP-8654
        $maxDeliveryDay   = $deliveryDays->longestMax - $_SESSION['Versandart']->nMaxLiefertage;
        $deliverydaylimit = $config->getValue('jtl_pack_deliverydaylimit');
        if (\is_numeric($deliverydaylimit) && $maxDeliveryDay > (int)$deliverydaylimit) {
            $result += $maxDeliveryDay;
        }

        return $result;
    }

    /**
     * @param      $zip
     * @param null $startdate
     * @return array{error: bool, status_code: int, dhl_service: object|null}
     */
    public function getAvailableDhlServices($zip, $startdate = null): array
    {
        $config         = $this->getPlugin()->getConfig();
        $logger         = Shop::Container()->getLogService();
        $excludedDays   = [];
        $dayMapping     = [
            'SO' => 0,
            'MO' => 1,
            'DI' => 2,
            'MI' => 3,
            'DO' => 4,
            'FR' => 5,
            'SA' => 6
        ];
        $deliveryDate   = Carbon::now();
        $noDeliveryDays = [];
        if ($config->getValue('jtl_pack_keineversandtage') !== null) {
            $noDeliveryDays = \explode(',', $config->getValue('jtl_pack_keineversandtage'));
        }
        foreach ($noDeliveryDays as $tmpDeliveryExclusion) {
            if (isset($dayMapping[\strtoupper($tmpDeliveryExclusion)])) {
                $excludedDays[] = $dayMapping[$tmpDeliveryExclusion];
            }
        }
        $manipulationTime = $this->getItemManipulationTime($config);
        $shipToday        = $manipulationTime === 0;
        $deliveryDate->addDays($manipulationTime);
        // is deliveryDate after adding workingtime and checking cutofftime at excluded day?
        while (\in_array($deliveryDate->format('w'), $excludedDays)) {
            $deliveryDate->addDay();
            $shipToday = false;
        }
        // Today is delivery day and also a valid working day
        if ($shipToday) {
            $cutOffTimeTmp = \explode(':', ($config->getValue('jtl_pack_cutofftime') ?? ''));
            if (\is_array($cutOffTimeTmp) && \count($cutOffTimeTmp) === 2) {
                // Check cutofftime
                if ((int)$deliveryDate->format('G') > (int)$cutOffTimeTmp[0]) {
                    // Hour is after cutofftimehour --> add day
                    $deliveryDate->addDay();
                } elseif ((int)$deliveryDate->format('G') === (int)$cutOffTimeTmp[0]) {
                    // Hour = cutofftimehour and minutes are after cutofftimeminutes
                    if ((int)$deliveryDate->format('i') > (int)$cutOffTimeTmp[1]) {
                        $deliveryDate->addDay();
                    }
                }
            }
        }
        $service   = $this->useSandbox === true ? 'sandbox' : 'production';
        $url       = \sprintf(
            'https://cig.dhl.de/services/%s/rest/checkout/%s/availableServices?startDate=%s',
            $service,
            $zip,
            $startdate ?? $deliveryDate->format('Y-m-d')
        );
        $apiResult = $this->callDhlApi($url);
        if ($this->isDebug() === true) {
            $logger->debug(
                'Plugin ' . $this->getPlugin()->getPluginID()
                . ' URL: ' . $url
                . ', API-Status-Code: ' . $apiResult['status_code']
                . ', Result: ' . $apiResult['result']
            );
        }
        if ($apiResult['status_code'] === '200') {
            $dhl_service = \json_decode($apiResult['result']);
            if ($dhl_service->preferredDay->available === false) {
                unset($_SESSION['wunschtag_selected']);
            }

            return [
                'error'              => false,
                'status_code'        => 200,
                'dhl_service'        => $dhl_service,
                'wunschtag_selected' => $_SESSION['wunschtag_selected'] ?? '0'
            ];
        }
        $_SESSION['wunschtag_selected'] = '0';
        $result                         = [];
        $result['error']                = true;
        $result['status_code']          = (int)$apiResult['status_code'];

        return $result;
    }

    public function checkDeliveryDate(string $deliveryDate): bool
    {
        $deliveryDaysAvailable = $this->getAvailableDhlServices($_SESSION['Lieferadresse']->cPLZ);
        $preferredDay          = $deliveryDaysAvailable['dhl_service']->preferredDay ?? null;
        if (
            $deliveryDaysAvailable['error'] === false
            && $preferredDay->available === true
        ) {
            foreach ($preferredDay->validDays as $day) {
                $tmpDate = Carbon::create($day->start);
                if ($tmpDate !== null && $tmpDate->format('d.m.Y') === $deliveryDate) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $value
     * @return IOResponse
     */
    public function setJtlPackLocation($value): IOResponse
    {
        $response          = new IOResponse();
        $result            = [];
        $result['success'] = true;
        if ($value === '') {
            unset($_SESSION['wunschlocation']);
            if ($this->isDebug()) {
                $result['action'] = 'unset session value';
            }
        } else {
            $_SESSION['wunschlocation'] = $value;
            if ($this->isDebug()) {
                $result['action'] = 'set session value';
            }
        }

        $response->script('this.response = ' . \json_encode($result) . ';');

        return $response;
    }

    /**
     * @param $type
     * @param $value
     * @return IOResponse
     */
    public function setJtlDeliveryWish($type, $value): IOResponse
    {
        $response = new IOResponse();
        if ($type === 'wunschtag') {
            if ($value !== '') {
                $_SESSION['wunschtag_selected'] = $value;
            } else {
                unset($_SESSION['wunschtag_selected']);
            }
        }
        $response->script('this.response = ' . \json_encode(['success' => true]) . ';');

        return $response;
    }

    /**
     *
     */
    public function setAdditionalCosts(): void
    {
        if (!isset($_SESSION['wunschtag_selected']) || $_SESSION['wunschtag_selected'] === '0') {
            return;
        }
        $additionalCostsVal = $this->getPlugin()->getConfig()->getValue('jtl_pack_wunschtag_costs');
        $currency           = Currency::fromISO(Shop::Lang()->gibISO());

        $additionalCostsVal *= $currency->getConversionFactor();
        if ($additionalCostsVal <= 0) {
            return;
        }
        $_SESSION['Warenkorb']->erstelleSpezialPos(
            $this->getPlugin()->getLocalization()->getTranslation('jtl_pack_pos_name'),
            1,
            $additionalCostsVal,
            1,
            \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
            false,
            true,
            ''
        );
    }

    /**
     * @param Bestellung $order
     */
    public function setOrderAttributes($order): void
    {
        $setFeederSystem = false;
        $jtlPack         = (int)($_SESSION['jtlPack'] ?? 0);
        if (isset($_SESSION['jtlPack'])) {
            $noServices = [-2, -3, -4];
            if (\in_array((int)$_SESSION['jtlPack'], $noServices)) {
                unset($_SESSION['wunschtag_selected'], $_SESSION['wunschlocation']);
            }
        }

        if ($this->getPlugin()->getConfig()->getValue('jtl_pack_wawiversion') === '14x') {
            if ($jtlPack === -4) {
                $bestellAttribut              = new stdClass();
                $bestellAttribut->kBestellung = $order->kBestellung;
                $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_salutation';
                $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cAnrede;
                $this->getDb()->insert('tbestellattribut', $bestellAttribut);

                $bestellAttribut              = new stdClass();
                $bestellAttribut->kBestellung = $order->kBestellung;
                $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_firstName';
                $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cVorname;
                $this->getDb()->insert('tbestellattribut', $bestellAttribut);

                $bestellAttribut              = new stdClass();
                $bestellAttribut->kBestellung = $order->kBestellung;
                $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_lastName';
                $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cNachname;
                $this->getDb()->insert('tbestellattribut', $bestellAttribut);

                $bestellAttribut              = new stdClass();
                $bestellAttribut->kBestellung = $order->kBestellung;
                $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_street';
                $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cStrasse;
                $this->getDb()->insert('tbestellattribut', $bestellAttribut);

                $bestellAttribut              = new stdClass();
                $bestellAttribut->kBestellung = $order->kBestellung;
                $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_houseNumber';
                $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cHausnummer;
                $this->getDb()->insert('tbestellattribut', $bestellAttribut);

                $bestellAttribut              = new stdClass();
                $bestellAttribut->kBestellung = $order->kBestellung;
                $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_adressAddition';
                $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cAdressZusatz;
                $this->getDb()->insert('tbestellattribut', $bestellAttribut);
                $setFeederSystem = true;
            }
        } elseif ($jtlPack === -4) {
            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_salutation';
            $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cAnrede;
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);

            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_first_name';
            $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cVorname;
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);

            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_last_name';
            $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cNachname;
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);

            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_street';
            $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cStrasse;
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);

            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_house_number';
            $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cHausnummer;
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);

            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_neighbour_address_addition';
            $bestellAttribut->cValue      = $_SESSION['Lieferadresse']->cAdressZusatz;
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);
            $setFeederSystem = true;
        }

        if (isset($_SESSION['wunschtag_selected']) && $_SESSION['wunschtag_selected'] !== '0') {
            $wishDate                     = Carbon::create($_SESSION['wunschtag_selected']);
            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_day';
            $bestellAttribut->cValue      = $wishDate->format('Y-m-d');
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);
            $setFeederSystem = true;
        }

        if (isset($_SESSION['wunschlocation'])) {
            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'dhl_wunschpaket_location';
            $bestellAttribut->cValue      = $_SESSION['wunschlocation'];
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);
            $setFeederSystem = true;
        }

        if ($setFeederSystem) {
            $bestellAttribut              = new stdClass();
            $bestellAttribut->kBestellung = $order->kBestellung;
            $bestellAttribut->cName       = 'feederSystem';
            $bestellAttribut->cValue      = 'jtl';
            $this->getDb()->insert('tbestellattribut', $bestellAttribut);
        }

        unset($_SESSION['jtlPack'], $_SESSION['wunschtag_selected'], $_SESSION['wunschlocation']);
    }

    public function checkDeliveryAddress(): void
    {
        if (isset($_SESSION['jtlPack'])) {
            return;
        }
        switch ($_SESSION['Lieferadresse']->cStrasse ?? '') {
            case 'Packstation':
                $_SESSION['jtlPack'] = -2;
                break;

            case 'Postfiliale':
                $_SESSION['jtlPack'] = -3;
                break;

            default:
                break;
        }
    }

    /**
     * @return DbInterface
     */
    public function getDb(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     * @return JtlPack
     */
    public function setDb(DbInterface $db): JtlPack
    {
        $this->db = $db;

        return $this;
    }

    /**
     * @param array|null $shippingMethods
     * @return array|void
     */
    public function filterShippingMethods(?array $shippingMethods = null)
    {
        $jtlPack = $_SESSION['jtlPack'] ?? null;
        $config  = $this->getPlugin()->getConfig();
        $smarty  = Shop::Smarty();
        if ($shippingMethods === null) {
            $shippingMethods = $smarty->getTemplateVars('Versandarten') ?? [];
            $smartyAssign    = true;
        } else {
            $smartyAssign = false;
        }

        if ($jtlPack === null || (int)$jtlPack >= -1) {
            $exclusiveShippingMethods = [];
            if ($config->getValue('jtl_pack_shippingmethods_packstation_exclusive') === 'Y') {
                $exclusiveShippingMethods = \array_merge(
                    $exclusiveShippingMethods,
                    $config->getValue('jtl_pack_shippingmethods_packstation')
                );
            }
            if ($config->getValue('jtl_pack_shippingmethods_filiale_exclusive') === 'Y') {
                $exclusiveShippingMethods = \array_merge(
                    $exclusiveShippingMethods,
                    $config->getValue('jtl_pack_shippingmethods_filiale')
                );
            }
            if ($config->getValue('jtl_pack_shippingmethods_neighbour_exclusive') === 'Y') {
                $exclusiveShippingMethods = \array_merge(
                    $exclusiveShippingMethods,
                    $config->getValue('jtl_pack_shippingmethods_neighbour')
                );
            }
            foreach ($shippingMethods as $key => $value) {
                if (\in_array((int)$value->kVersandart, \array_map('\intval', $exclusiveShippingMethods), true)) {
                    unset($shippingMethods[$key]);
                }
            }
        }

        if ($jtlPack !== null && $jtlPack <= -2) {
            $allowedShippingMethodIds = [];
            switch ($jtlPack) {
                case -2:
                    $allowedShippingMethodIds = $config->getValue('jtl_pack_shippingmethods_packstation');
                    break;

                case -3:
                    $allowedShippingMethodIds = $config->getValue('jtl_pack_shippingmethods_filiale');
                    break;

                case -4:
                    $allowedShippingMethodIds = $config->getValue('jtl_pack_shippingmethods_neighbour');
                    break;
            }
            foreach ($shippingMethods as $key => $value) {
                if (!\in_array((int)$value->kVersandart, \array_map('\intval', $allowedShippingMethodIds), true)) {
                    unset($shippingMethods[$key]);
                }
            }
        }

        if (!$smartyAssign) {
            return $shippingMethods;
        }

        unset($_SESSION['Versandart']);
        if (\count($shippingMethods) === 0) {
            $smarty->clearAssign('Versandarten');
        } else {
            $smarty->assign('Versandarten', $shippingMethods)
                ->assign('AktiveVersandart', $this->getActiveShippingMethod($shippingMethods));
        }
        $paymentMethods = $smarty->getTemplateVars('Zahlungsarten');
        if ($paymentMethods !== null) {
            $smarty->assign('AktiveZahlungsart', $this->getActivePaymentMethod($paymentMethods));
        }
    }

    private function getShippingClasses(Cart $cart): string
    {
        if ($this->useShippingService()) {
            return \implode(
                '-',
                Shop::Container()->getShippingService()->getShippingClasses($cart->PositionenArr)
            );
        }

        return \JTL\Helpers\ShippingMethod::getShippingClasses($cart);
    }

    /**
     * @param array|bool $shippingMethodIds
     * @return bool
     */
    public function isShippingUsable($shippingMethodIds): bool
    {
        if (!\is_array($shippingMethodIds) || (\count($shippingMethodIds) === 0)) {
            return false;
        }
        $scRegEx = '/^([0-9 -]* )?' . $this->getShippingClasses(Frontend::getCart()) . ' /';
        foreach (\array_map('\intval', $shippingMethodIds) as $id) {
            if ($id === -1) {
                return true;
            }
            $tmpShippingMethod = new Versandart($id);
            $customerGroups    = \array_map(
                '\intval',
                \explode(';', \trim($tmpShippingMethod->cKundengruppen ?? '', ';'))
            );
            if (
                ((int)$tmpShippingMethod->cVersandklassen === -1
                    || \preg_match($scRegEx, $tmpShippingMethod->cVersandklassen)
                )
                && (($customerGroups[0] ?? -1) === -1
                    || \in_array(Frontend::getCustomerGroup()->getID(), $customerGroups, true)
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $paymentMethods
     * @return int
     * @since 2.2.1
     * @former gibAktiveZahlungsart()
     */
    private function getActivePaymentMethod(array $paymentMethods): int
    {
        if (empty($paymentMethods)) {
            return 0;
        }
        if (isset($_SESSION['Zahlungsart'])) {
            $_SESSION['AktiveZahlungsart'] = $_SESSION['Zahlungsart']->kZahlungsart;
        } elseif (!empty($_SESSION['AktiveZahlungsart']) && GeneralObject::hasCount($paymentMethods)) {
            $active  = (int)$_SESSION['AktiveZahlungsart'];
            $reduced = \array_reduce($paymentMethods, static function ($carry, $item) use ($active) {
                return (int)$item->kZahlungsart === $active ? (int)$item->kZahlungsart : $carry;
            }, 0);
            if ($reduced !== (int)$_SESSION['AktiveZahlungsart']) {
                $_SESSION['AktiveZahlungsart'] = $paymentMethods[0]->kZahlungsart;
            }
        } else {
            $_SESSION['AktiveZahlungsart'] = $paymentMethods[0]->kZahlungsart;
        }

        return (int)$_SESSION['AktiveZahlungsart'];
    }

    private function useShippingService(): bool
    {
        return \method_exists(Shop::Container(), 'getShippingService');
    }

    /**
     * @param stdClass[] $shippingMethods
     */
    private function getFirstShippingMethodID(array $shippingMethods, int $customerGroupID, int $paymentMethodID): int
    {
        if ($this->useShippingService()) {
            return Shop::Container()->getShippingService()->getFirstShippingMethod(
                \array_map(
                    static fn($legacyShippingMethod) => \JTL\Shipping\DomainObjects\ShippingDTO::fromLegacyObject(
                        $legacyShippingMethod
                    ),
                    $shippingMethods
                ),
                $customerGroupID,
                $paymentMethodID
            )->id ?? 0;
        }

        return (int)(\JTL\Helpers\ShippingMethod::getFirstShippingMethod(
            $shippingMethods,
            $paymentMethodID
        )->kVersandart ?? '0');
    }

    /**
     * @param stdClass|object[] $shippingMethods
     * @return int
     * @since 2.2.1
     * @former gibAktiveVersandart()
     */
    private function getActiveShippingMethod(array $shippingMethods): int
    {
        if (isset($_SESSION['Versandart'])) {
            $_SESSION['AktiveVersandart'] = (int)$_SESSION['Versandart']->kVersandart;
        } elseif (!empty($_SESSION['AktiveVersandart']) && GeneralObject::hasCount($shippingMethods)) {
            $active  = (int)$_SESSION['AktiveVersandart'];
            $reduced = \array_reduce($shippingMethods, static function ($carry, $item) use ($active) {
                return (int)$item->kVersandart === $active ? (int)$item->kVersandart : $carry;
            }, 0);
            if ($reduced !== (int)$_SESSION['AktiveVersandart']) {
                $_SESSION['AktiveVersandart'] = $this->getFirstShippingMethodID(
                    $shippingMethods,
                    Frontend::getCustomer()->getGroupID(),
                    (int)($_SESSION['Zahlungsart']->kZahlungsart ?? '0')
                );
            }
        } else {
            $_SESSION['AktiveVersandart'] = $this->getFirstShippingMethodID(
                $shippingMethods,
                Frontend::getCustomer()->getGroupID(),
                (int)($_SESSION['Zahlungsart']->kZahlungsart ?? '0')
            );
        }

        return (int)$_SESSION['AktiveVersandart'];
    }
}
