<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Refund;
use Plugin\s360_amazonpay_shop5\lib\Entities\AccountMapping;
use JTL\DB\ReturnType;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;

/**
 * Class Database
 *
 * Handles all database access to the JTL database.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class Database {

    /**
     * @var \JTL\DB\DbInterface
     */
    private $database;

    public const PLUGIN_TABLE_PREFIX = 'xplugin_' . Constants::PLUGIN_ID . '_';
    public const PLUGIN_TABLE_NAME_CONFIG = self::PLUGIN_TABLE_PREFIX . 'config';
    public const PLUGIN_TABLE_NAME_ACCOUNTMAPPING = self::PLUGIN_TABLE_PREFIX . 'accountmapping';
    public const PLUGIN_TABLE_NAME_CHARGEPERMISSION = self::PLUGIN_TABLE_PREFIX . 'chargepermission';
    public const PLUGIN_TABLE_NAME_CHARGE = self::PLUGIN_TABLE_PREFIX . 'charge';
    public const PLUGIN_TABLE_NAME_REFUND = self::PLUGIN_TABLE_PREFIX . 'refund';
    public const PLUGIN_TABLE_NAME_SUBSCRIPTION = self::PLUGIN_TABLE_PREFIX . 'subscription';
    public const PLUGIN_TABLE_NAME_SUBSCRIPTION_ORDER = self::PLUGIN_TABLE_PREFIX . 'subscription_order';

    public const ORDER_DEFAULT_SORTING = 'shopOrderId';
    public const ORDER_DEFAULT_SORTING_DIRECTION = 'DESC';
    public const ORDER_SORTINGS = [
        'shopOrderId' => 'shopOrderId',
        'shopOrderNumber' => 'shopOrderNumber',
        'shopOrderStatus' => 'tbestellung.cStatus',
        'chargePermissionId' => 'chargePermissionId',
        'chargePermissionStatus' => 'status',
        'chargePermissionAmount' => 'chargeAmountLimitAmount',
        'chargePermissionExpiration' => 'expirationTimestamp'
    ];

    /**
     * @var Database
     */
    private static $instance;

    /**
     * Get the plugin specific database instance.
     * @return Database
     */
    public static function getInstance(): Database {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->database = Shop::Container()->getDB();
    }

    /**
     * Inserts a config value into the database table or updates it, if it exists.
     * @param string $key
     * @param string $value
     */
    public function upsertConfig(string $key, string $value): void {
        $obj = new \stdClass();
        $obj->configKey = $key;
        $obj->configValue = $value;

        // check if value exists
        $result = $this->database->select(self::PLUGIN_TABLE_NAME_CONFIG, 'configKey', $key);
        if (empty($result)) {
            // insert
            $this->database->insert(self::PLUGIN_TABLE_NAME_CONFIG, $obj);
        } else {
            // update
            $this->database->update(self::PLUGIN_TABLE_NAME_CONFIG, 'id', $result->id, $obj);
        }
    }

    /**
     * Returns a config setting from the configuration table or null if it was not found.
     * @param string $key
     * @return mixed|null
     */
    public function getConfigSetting(string $key):?string {
        if (!empty($key)) {
            $result = $this->database->select(self::PLUGIN_TABLE_NAME_CONFIG, 'configKey', $key);
            if (!empty($result)) {
                return $result->configValue;
            }
        }
        return null;
    }

    /**
     * Returns the complete config data.
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getConfig(): array {
        $stmt = 'SELECT * FROM ' . self::PLUGIN_TABLE_NAME_CONFIG;
        $result = $this->database->executeQueryPrepared($stmt, [], ReturnType::ARRAY_OF_ASSOC_ARRAYS);
        if (empty($result)) {
            return [];
        }
        return array_map(function ($element) {
            return [$element['configKey'] => $element['configValue']];
        }, $result);
    }

    /**
     * Removes a customer account mapping for the given jtl customer id.
     *
     * @param $customerId
     */
    public function deleteAccountMappingForJtlCustomerId($customerId): void {
        $this->database->delete(self::PLUGIN_TABLE_NAME_ACCOUNTMAPPING, 'jtlCustomerId', $customerId);
    }

    /**
     * Gets an account mapping for the given amazon user id.
     * @param $amazonUserId
     * @return mixed|null
     */
    public function findAccountMapping(string $amazonUserId): ?AccountMapping {
        if (empty($amazonUserId)) {
            return null;
        }
        $result = $this->database->select(self::PLUGIN_TABLE_NAME_ACCOUNTMAPPING, ['amazonUserId'], [$amazonUserId]);
        if (empty($result)) {
            return null;
        }
        return new AccountMapping($result);
    }

    /**
     * Inserts a new Account Mapping into the database.
     * @param AccountMapping $mappingData
     * @return int
     */
    public function insertMappingData(AccountMapping $mappingData): int {
        $object = $mappingData->getDatabaseObject();
        return $this->database->insert(self::PLUGIN_TABLE_NAME_ACCOUNTMAPPING, $object);
    }

    public function updateMappingData(AccountMapping $mappingData) {
        $object = $mappingData->getDatabaseObject();
        return $this->database->update(self::PLUGIN_TABLE_NAME_ACCOUNTMAPPING, 'id', $mappingData->getId(), $object);
    }

    /**
     * Finds a *real* customer in the database by his email address. "Real" means, not a guest.
     * @param $amazonEmail
     * @return mixed|null
     */
    public function findCustomerByEmail($amazonEmail) {
        $result = $this->database->select('tkunde', 'cMail', $amazonEmail, 'nRegistriert', 1);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public function findCustomerByJtlCustomerId($jtlCustomerId) {
        $result = $this->database->select('tkunde', 'kKunde', $jtlCustomerId);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    /**
     * Gets the key for the shipping address from the given JTL Kunde and Lieferadresse.
     * @param $jtlCustomer
     * @param $jtlAddress
     * @param bool $ignoreEmail - flag if email should be ignored for comparison
     * @return int
     */
    public function determineKeyForShippingAddress($jtlCustomer, $jtlAddress, $ignoreEmail = false): int {
        if (empty($jtlCustomer) || (int)$jtlCustomer->kKunde <= 0) {
            return -1;
        }
        $sql = 'SELECT * FROM tlieferadresse WHERE kKunde = :kKunde';
        $result = $this->database->queryPrepared($sql, ['kKunde' => (int)$jtlCustomer->kKunde], ReturnType::ARRAY_OF_OBJECTS);
        if (!empty($result) && \is_array($result)) {
            foreach ($result as $res) {
                // load the lieferadresse specifically - this is needed to decode the encoded part
                $candidateShippingAddress = new Lieferadresse((int)$res->kLieferadresse);
                /*
                 * now match, we accept this as equal only if all the following data matches:
                 *
                 * NOT var $cAnrede; (Amazon Pay does not provide us with this information, but JTL will set it nevertheless, so we could never match, if we tried to look at this)
                 * var $cVorname;
                 * var $cNachname;
                 * var $cTitel;
                 * var $cFirma;
                 * var $cStrasse;
                 * var $cAdressZusatz;
                 * var $cPLZ;
                 * var $cOrt;
                 * var $cBundesland;
                 * var $cLand;
                 * var $cTel;
                 * var $cMobil;
                 * var $cFax;
                 * var $cMail;
                 * var $cHausnummer;
                 * var $cZusatz;
                 */
                $equal = true;
                $equal = $equal && ((empty($candidateShippingAddress->cVorname) && empty($jtlAddress->cVorname)) || $candidateShippingAddress->cVorname === $jtlAddress->cVorname);
                $equal = $equal && ((empty($candidateShippingAddress->cNachname) && empty($jtlAddress->cNachname)) || $candidateShippingAddress->cNachname === $jtlAddress->cNachname);
                $equal = $equal && ((empty($candidateShippingAddress->cTitel) && empty($jtlAddress->cTitel)) || $candidateShippingAddress->cTitel === $jtlAddress->cTitel);
                $equal = $equal && ((empty($candidateShippingAddress->cFirma) && empty($jtlAddress->cFirma)) || $candidateShippingAddress->cFirma === $jtlAddress->cFirma);
                $equal = $equal && ((empty($candidateShippingAddress->cStrasse) && empty($jtlAddress->cStrasse)) || $candidateShippingAddress->cStrasse === $jtlAddress->cStrasse);
                $equal = $equal && ((empty($candidateShippingAddress->cAdressZusatz) && empty($jtlAddress->cAdressZusatz)) || $candidateShippingAddress->cAdressZusatz === $jtlAddress->cAdressZusatz);
                $equal = $equal && ((empty($candidateShippingAddress->cPLZ) && empty($jtlAddress->cPLZ)) || $candidateShippingAddress->cPLZ === $jtlAddress->cPLZ);
                $equal = $equal && ((empty($candidateShippingAddress->cOrt) && empty($jtlAddress->cOrt)) || $candidateShippingAddress->cOrt === $jtlAddress->cOrt);
                $equal = $equal && ((empty($candidateShippingAddress->cBundesland) && empty($jtlAddress->cBundesland)) || $candidateShippingAddress->cBundesland === $jtlAddress->cBundesland);
                $equal = $equal && ((empty($candidateShippingAddress->cLand) && empty($jtlAddress->cLand)) || $candidateShippingAddress->cLand === $jtlAddress->cLand);
                $equal = $equal && ((empty($candidateShippingAddress->cTel) && empty($jtlAddress->cTel)) || $candidateShippingAddress->cTel === $jtlAddress->cTel);
                $equal = $equal && ((empty($candidateShippingAddress->cMobil) && empty($jtlAddress->cMobil)) || $candidateShippingAddress->cMobil === $jtlAddress->cMobil);
                $equal = $equal && ((empty($candidateShippingAddress->cFax) && empty($jtlAddress->cFax)) || $candidateShippingAddress->cFax === $jtlAddress->cFax);
                $equal = $equal && ($ignoreEmail  || (empty($candidateShippingAddress->cMail) && empty($jtlAddress->cMail)) || $candidateShippingAddress->cMail === $jtlAddress->cMail);
                $equal = $equal && ((empty($candidateShippingAddress->cHausnummer) && empty($jtlAddress->cHausnummer)) || $candidateShippingAddress->cHausnummer === $jtlAddress->cHausnummer);
                $equal = $equal && ((empty($candidateShippingAddress->cZusatz) && empty($jtlAddress->cZusatz)) || $candidateShippingAddress->cZusatz === $jtlAddress->cZusatz);
                if ($equal) {
                    return (int)$candidateShippingAddress->kLieferadresse;
                }
            }
            // if we get to this point, we found nothing
        }
        return -1;
    }

    /**
     * Saves additional order attributes on an order.
     * @param Bestellung $jtlOrder
     * @param ChargePermission $chargePermission
     * @param Charge $charge
     */
    public function saveOrderAttributes(Bestellung $jtlOrder, ChargePermission $chargePermission, Charge $charge): void {
        $orderAttribute = new \stdClass();
        $orderAttribute->kBestellung = $jtlOrder->kBestellung;
        $orderAttribute->cName = Constants::ORDER_ATTRIBUTE_REFERENCE_ID;
        $orderAttribute->cValue = $chargePermission->getChargePermissionId();
        $this->database->insert('tbestellattribut', $orderAttribute);
    }

    public function saveSubscriptionOrderAttribute(Bestellung $jtlOrder, Subscription $subscription, $flag) {

        $interval = $subscription->getInterval();
        $attributeFlag = Config::getInstance()->getSubscriptionOrderAttributeFlag();
        $attributeInterval =  Config::getInstance()->getSubscriptionOrderAttributeInterval();

        if($interval === null || empty($attributeFlag) || empty($attributeInterval)) {
            return;
        }

        $orderAttribute = new \stdClass();
        $orderAttribute->kBestellung = $jtlOrder->kBestellung;
        $orderAttribute->cName = $attributeFlag;
        $orderAttribute->cValue = $flag;
        $this->database->insert('tbestellattribut', $orderAttribute);

        $orderAttribute = new \stdClass();
        $orderAttribute->kBestellung = $jtlOrder->kBestellung;
        $orderAttribute->cName = $attributeInterval;
        $orderAttribute->cValue = $interval->toString();
        $this->database->insert('tbestellattribut', $orderAttribute);
    }

    /**
     * Gets the generated UID for a order.
     * @param Bestellung $order
     * @return null|string
     */
    public function getUidForOrder(Bestellung $order): ?string {
        $result = $this->database->select('tbestellid', 'kBestellung', $order->kBestellung);
        if (empty($result)) {
            return null;
        }
        return $result->cId;
    }

    /**
     * Sets a jtl order to pending by setting its cAbgeholt to Y
     * @param $kBestellung
     * @throws \InvalidArgumentException
     */
    public function setOrderPending(int $kBestellung): void {
        $this->database->executeQueryPrepared('UPDATE tbestellung SET cAbgeholt="Y" WHERE kBestellung = :kBestellung', ['kBestellung' => $kBestellung], ReturnType::DEFAULT);
    }

    /**
     * Loads charge permissions paginated, including some information from tbestellung
     * @param $offset
     * @param $limit
     * @return array|bool|int|object
     */
    public function loadChargePermissions($offset, $limit, $sorting = 'shopOrderId', $sortingDirection = 'DESC', $statusFilters = [], $statusReasonFilters = []) {
        if(\array_key_exists($sorting, self::ORDER_SORTINGS)) {
            $sorting = self::ORDER_SORTINGS[$sorting];
        } else {
            $sorting = self::ORDER_DEFAULT_SORTING;
        }
        if($sortingDirection !== 'DESC' && $sortingDirection !== 'ASC') {
            $sortingDirection = self::ORDER_DEFAULT_SORTING_DIRECTION;
        }
        if(!\is_array($statusFilters) || empty($statusFilters)) {
            $statusFilters = [];
        }
        if(!\is_array($statusReasonFilters) || empty($statusReasonFilters)) {
            $statusReasonFilters = [];
        }
        $whereClause = '';
        $statusParameters = [];
        $statusFilterCount = 0;
        $statusReasonParameters = [];
        $statusReasonFilterCount = 0;
        if(!empty($statusFilters) || !empty($statusReasonFilters)) {
            $whereClause = ' WHERE ';
            if(!empty($statusFilters)) {
                foreach($statusFilters as $statusFilter) {
                    if($statusFilterCount > 0) {
                        $whereClause .= ' OR ';
                    } else {
                        $whereClause .= '(';
                    }
                    $whereClause .= 'status LIKE :status' . $statusFilterCount;
                    $statusParameters['status' . $statusFilterCount] = $statusFilter; // No wildcards here because there can be only one state and we dont want to show NonChargeable when Chargeable was selected
                    $statusFilterCount++;
                }
                $whereClause .= ')';
            }
            if(!empty($statusReasonFilters)) {
                foreach($statusReasonFilters as $statusReasonFilter) {
                    if($statusFilterCount > 0 && $statusReasonFilterCount === 0) {
                        $whereClause .= ' AND (';
                    } else if($statusReasonFilterCount === 0) {
                        $whereClause .= '(';
                    }
                    if($statusReasonFilterCount > 0) {
                        $whereClause .= ' OR ';
                    }
                    $whereClause .= 'statusReason LIKE :statusReason' . $statusReasonFilterCount;
                    $statusReasonParameters['statusReason' . $statusReasonFilterCount] = '%'.$statusReasonFilter.'%';
                    $statusReasonFilterCount++;
                }
                $whereClause .= ')';
            }
        }
        $orderByClause = $sorting . ' ' . $sortingDirection;
        $sqlParameters = ['limit' => $limit, 'offset' => $offset];
        foreach($statusParameters as $k => $v) {
            $sqlParameters[$k] = $v;
        }
        foreach($statusReasonParameters as $k => $v) {
            $sqlParameters[$k] = $v;
        }
        $sql = 'SELECT * FROM ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . ' LEFT JOIN tbestellung ON tbestellung.kBestellung = ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . '.shopOrderId '. $whereClause .' ORDER BY ' . $orderByClause .' LIMIT :limit OFFSET :offset';
        $result = $this->database->executeQueryPrepared($sql, $sqlParameters, ReturnType::ARRAY_OF_OBJECTS);
        if (empty($result)) {
            return [];
        }
        return $result;
    }

    /**
     * Searches orders by chargepermissionid or shopOrderNumber
     * @param $searchValue
     * @return array|bool|int|object
     */
    public function searchChargePermissions($searchValue) {
        if (empty($searchValue)) {
            // we do not allow empty searches
            return [];
        }
        $sql = 'SELECT * FROM ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . ' LEFT JOIN tbestellung ON tbestellung.kBestellung = ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . '.shopOrderId  WHERE shopOrderNumber LIKE :searchValue OR chargePermissionId LIKE :searchValue ORDER BY shopOrderId DESC LIMIT 100';
        $result = $this->database->executeQueryPrepared($sql, ['searchValue' => '%' . $searchValue . '%'], ReturnType::ARRAY_OF_OBJECTS);
        if (empty($result)) {
            $result = [];
            // try to search for the order number within charges of recurring orders
            $sqlSubscriptionCharges = 'SELECT * FROM  '. self::PLUGIN_TABLE_NAME_CHARGE.' LEFT JOIN tbestellung ON tbestellung.kBestellung = ' . self::PLUGIN_TABLE_NAME_CHARGE . '.shopOrderId WHERE cBestellNr LIKE :searchValue ORDER BY cBestellNr DESC LIMIT 100';
            $subscriptionCharges = $this->database->executeQueryPrepared($sqlSubscriptionCharges, ['searchValue' => '%' . $searchValue . '%'], ReturnType::ARRAY_OF_OBJECTS);
            if(!empty($subscriptionCharges)) {
                foreach($subscriptionCharges as $subscription) {
                    $result[] = $this->database->executeQueryPrepared('SELECT * FROM ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . ' LEFT JOIN tbestellung ON tbestellung.kBestellung = ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . '.shopOrderId WHERE chargePermissionId = :chargePermissionId', ['chargePermissionId' => $subscription->chargePermissionId], ReturnType::SINGLE_OBJECT);
                }
            }
        }
        return $result;
    }

    public function loadChargePermission($chargePermissionId, bool $returnAsStdClass = false) {
        $result = $this->database->select(self::PLUGIN_TABLE_NAME_CHARGEPERMISSION, 'chargePermissionId', $chargePermissionId);
        if (empty($result)) {
            return null;
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return (new ChargePermission())->fillFromDatabaseObject($result);
    }

    public function loadCharge($chargeId, bool $returnAsStdClass = false) {
        $result = $this->database->select(self::PLUGIN_TABLE_NAME_CHARGE, 'chargeId', $chargeId);
        if (empty($result)) {
            return null;
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return (new Charge())->fillFromDatabaseObject($result);
    }

    public function loadRefund($refundId, bool $returnAsStdClass = false) {
        $result = $this->database->select(self::PLUGIN_TABLE_NAME_REFUND, 'refundId', $refundId);
        if (empty($result)) {
            return null;
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return (new Refund())->fillFromDatabaseObject($result);
    }

    public function loadChargesForChargePermission($chargePermissionId, bool $returnAsStdClass = false) {
        $result = $this->database->selectAll(self::PLUGIN_TABLE_NAME_CHARGE, 'chargePermissionId', $chargePermissionId, '*', 'creationTimestamp ASC');
        if (empty($result)) {
            return [];
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return array_map(function ($object) {
            return (new Charge())->fillFromDatabaseObject($object);
        }, $result);
    }

    public function loadChargesByJtlOrderId($shopOrderId, bool $returnAsStdClass = false) {
        $result = $this->database->selectAll(self::PLUGIN_TABLE_NAME_CHARGE, 'shopOrderId', $shopOrderId, '*', 'creationTimestamp ASC');
        if (empty($result)) {
            return [];
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return array_map(static function ($object) {
            return (new Charge())->fillFromDatabaseObject($object);
        }, $result);
    }

    public function loadRefundsForCharge($chargeId, bool $returnAsStdClass = false) {
        $result = $this->database->selectAll(self::PLUGIN_TABLE_NAME_REFUND, 'chargeId', $chargeId);
        if (empty($result)) {
            return [];
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return array_map(function ($object) {
            return (new Refund())->fillFromDatabaseObject($object);
        }, $result);
    }

    /**
     * Sets an order such that the ERP can collect it.
     * IMPORTANT: We only release orders that are in the initial order creation state (BESTELLUNG_STATUS_OFFEN) of the JTL Shop to prevent having the ERP retrieve an order multiple times.
     * @param $chargePermissionId
     */
    public function releaseOrderForErp($chargePermissionId) {
        // first get the order reference itself to get the right jtl order id
        $chargePermission = $this->loadChargePermission($chargePermissionId, true);
        if (!empty($chargePermission)) {
            $jtlOrderId = $chargePermission->shopOrderId;
            if (!empty($jtlOrderId)) {
                $object = new \stdClass();
                $object->cAbgeholt = 'N';
                $this->database->update('tbestellung', ['kBestellung', 'cStatus'], [$jtlOrderId, BESTELLUNG_STATUS_OFFEN], $object);
            }
        }
    }

    /**
     * Loads a JTL Order (Bestellung) for the given chargePermissionId.
     * @param $chargePermissionId
     * @param bool $loadFullOrder
     * @return Bestellung|null
     */
    public function loadJtlOrderForChargePermissionId($chargePermissionId, bool $loadFullOrder = true) :?Bestellung {
        $chargePermission = $this->loadChargePermission($chargePermissionId, true);
        /** @noinspection MissingIssetImplementationInspection */
        if (!empty($chargePermission) && !empty($chargePermission->shopOrderId)) {
            return new Bestellung((int) $chargePermission->shopOrderId, $loadFullOrder);
        }
        return null;
    }

    /**
     * Loads a chargePermission by the given JtlOrderId, or returns null if none exists.
     * @param int $kBestellung
     * @return null|ChargePermission
     */
    public function loadChargePermissionByJtlOrderId(int $kBestellung): ?ChargePermission {
        $result = $this->database->select(self::PLUGIN_TABLE_NAME_CHARGEPERMISSION, 'shopOrderId', $kBestellung);
        if (empty($result)) {
            return null;
        }
        return (new ChargePermission())->fillFromDatabaseObject($result);
    }

    /**
     * Loads a chargePermission by the given JTL Order Id for subscription orders
     * @param int $kBestellung
     * @return ChargePermission|null
     */
    public function loadChargePermissionForSubscriptionOrderId(int $kBestellung): ?ChargePermission {
        $result = null;
        $initialOrderResult = $this->database->select(self::PLUGIN_TABLE_NAME_SUBSCRIPTION_ORDER, 'shopOrderId', $kBestellung);
        if(!empty($initialOrderResult) && !empty($initialOrderResult->initialShopOrderId)) {
            // hit - we can use the initial order id to find the charge permission instead
            $result = $this->database->select(self::PLUGIN_TABLE_NAME_CHARGEPERMISSION, 'shopOrderId', $initialOrderResult->initialShopOrderId);
        }
        if (empty($result)) {
            return null;
        }
        return (new ChargePermission())->fillFromDatabaseObject($result);
    }

    public function getChargePermissionsByState($states, bool $returnAsStdClass = false): array {
        $result = $this->database->executeQueryPrepared('SELECT * FROM ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . ' WHERE status IN (' . implode(',', $this->addQuotesToStringArray($states)) . ')', [], ReturnType::ARRAY_OF_OBJECTS);
        if (empty($result)) {
            return [];
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return array_map(function ($object) {
            return (new ChargePermission())->fillFromDatabaseObject($object);
        }, $result);
    }

    public function getChargesByState($states, bool $returnAsStdClass = false): array {
        $result = $this->database->executeQueryPrepared('SELECT * FROM ' . self::PLUGIN_TABLE_NAME_CHARGE . ' WHERE status IN (' . implode(',', $this->addQuotesToStringArray($states)) . ')', [], ReturnType::ARRAY_OF_OBJECTS);
        if (empty($result)) {
            return [];
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return array_map(function ($object) {
            return (new Charge())->fillFromDatabaseObject($object);
        }, $result);
    }

    public function getRefundsByState($states, bool $returnAsStdClass = false): array {
        $result = $this->database->executeQueryPrepared('SELECT * FROM ' . self::PLUGIN_TABLE_NAME_REFUND . ' WHERE status IN (' . implode(',', $this->addQuotesToStringArray($states)) . ')', [], ReturnType::ARRAY_OF_OBJECTS);
        if (empty($result)) {
            return [];
        }
        if ($returnAsStdClass) {
            return $result;
        }
        return array_map(function ($object) {
            return (new Refund())->fillFromDatabaseObject($object);
        }, $result);
    }

    private function addQuotesToStringArray($array): array {
        return array_map(static function ($element) {
            return '"' . $element . '"';
        }, $array);
    }

    public function saveChargePermission(ChargePermission $chargePermission, Bestellung $order): void {
        $object = $chargePermission->getDatabaseObject();
        $object->shopOrderId = $order->kBestellung;
        $object->shopOrderNumber = $order->cBestellNr;
        $this->insertOrUpdate(self::PLUGIN_TABLE_NAME_CHARGEPERMISSION, 'chargePermissionId', $object->chargePermissionId, $object);
    }

    public function saveCharge(Charge $charge): void {
        $object = $charge->getDatabaseObject();
        $this->insertOrUpdate(self::PLUGIN_TABLE_NAME_CHARGE, 'chargeId', $object->chargeId, $object);
    }

    public function saveRefund(Refund $refund): void {
        $object = $refund->getDatabaseObject();
        $this->insertOrUpdate(self::PLUGIN_TABLE_NAME_REFUND, 'refundId', $object->refundId, $object);
    }

    /**
     * @param $tableName
     * @param $keyName
     * @param $keyValue
     * @param $object
     */
    private function insertOrUpdate($tableName, $keyName, $keyValue, $object): void {
        if (empty($this->database->select($tableName, $keyName, $keyValue))) {
            $this->database->insert($tableName, $object);
        } else {
            $this->database->update($tableName, $keyName, $keyValue, $object);
        }
    }

    private function getDeliveriesForDeliveryNote($kLieferschein) {
        $deliveries = $this->database->selectAll('tversand', ['kLieferschein'], [(int)$kLieferschein]);
        if(!empty($deliveries)) {
            return $deliveries;
        }
        return [];
    }

    private function getDeliveryNotesForOrder($kBestellung) {
        $deliveryNotes = $this->database->selectAll('tlieferschein', ['kInetBestellung'], [(int)$kBestellung]);
        if(!empty($deliveryNotes)) {
            return $deliveryNotes;
        }
        return [];
    }

    public function getDeliveriesForOrder($kBestellung): array {
        $result = [];
        $deliveryNotes = $this->getDeliveryNotesForOrder($kBestellung);
        foreach($deliveryNotes as $deliveryNote) {
            $deliveries = $this->getDeliveriesForDeliveryNote($deliveryNote->kLieferschein);
            foreach($deliveries as $delivery) {
                $result[] = $delivery;
            }
        }
        return $result;
    }

    public function getExistingChargePermissionStates() {
        $result = [];
        $states = $this->database->executeQueryPrepared('SELECT DISTINCT status FROM ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . ' WHERE status IS NOT NULL AND status != "" ORDER BY status ASC', [], ReturnType::ARRAY_OF_OBJECTS);
        if(!empty($states)) {
            $result = \array_map(static function($statusResult) {
                return $statusResult->status;
            }, $states);
        }
        return $result;
    }

    public function getExistingChargePermissionStateReasons() {
        $result = [];
        $stateReasons = $this->database->executeQueryPrepared('SELECT DISTINCT statusReason FROM ' . self::PLUGIN_TABLE_NAME_CHARGEPERMISSION . ' WHERE statusReason IS NOT NULL AND statusReason != "" ORDER BY statusReason ASC', [], ReturnType::ARRAY_OF_OBJECTS);
        if(!empty($stateReasons)) {
            $result = \array_map(static function($stateReasonResult) {
                return $stateReasonResult->statusReason;
            }, $stateReasons);
        }
        return $result;
    }

    public function upsertSubscription(Subscription $subscription): ?Subscription {
        if($subscription->getId() === null || $subscription->getId() <= 0) {
            // insert
            $newId = $this->database->insert(self::PLUGIN_TABLE_NAME_SUBSCRIPTION, $subscription->getDatabaseObject());
            if($newId > 0) {
                $subscription->setId($newId);
                return $subscription;
            }
            return null;
        }

        // update
        $result = $this->database->update(self::PLUGIN_TABLE_NAME_SUBSCRIPTION, 'id', $subscription->getId(), $subscription->getDatabaseObject());
        if($result > 0) {
            return $subscription;
        }
        return null;
    }

    public function addSubscriptionOrder(int $subscriptionId, int $shopOrderId, int $initialShopOrderId) {
        $this->database->insert(self::PLUGIN_TABLE_NAME_SUBSCRIPTION_ORDER, (object) [
            'subscriptionId' => $subscriptionId,
            'shopOrderId' => $shopOrderId,
            'initialShopOrderId' => $initialShopOrderId
        ]);
    }

    public function selectSubscriptionById(int $subscriptionId): ?Subscription {
        $result = $this->database->select(self::PLUGIN_TABLE_NAME_SUBSCRIPTION, 'id', $subscriptionId);
        if(empty($result)) {
            return null;
        }
        return new Subscription($result);
    }

    public function selectSubscriptionsByCustomerId(int $jtlCustomerId): array {
        $result = $this->database->selectAll(self::PLUGIN_TABLE_NAME_SUBSCRIPTION, 'jtlCustomerId', $jtlCustomerId);
        if(empty($result)) {
            return [];
        }
        return array_map(static function($res) {
            return new Subscription($res);
        }, $result);
    }

    public function getShopOrder(int $id) {
        $result = $this->database->select('tbestellung', 'kBestellung', $id);
        return empty($result) ? null : $result;
    }

    public function getShopCustomer(int $id) {
        $result = $this->database->select('tkunde', 'kKunde', $id);
        return empty($result) ? null : $result;
    }

    public function getShopOrderAttribute(int $id) {
        $result = $this->database->select('tbestellattribut', 'kBestellattribut', $id);
        return empty($result) ? null : $result;
    }

    public function getShopCart(int $id) {
        $result = $this->database->select('twarenkorb', 'kWarenkorb', $id);
        return empty($result) ? null : $result;
    }

    public function getShopCartPosition(int $id) {
        $result = $this->database->select('twarenkorbpos', 'kWarenkorbPos', $id);
        return empty($result) ? null : $result;
    }

    public function getShopCartPositions(int $id): array {
        $result = $this->database->selectAll('twarenkorbpos', 'kWarenkorb', $id);
        return empty($result) ? [] : $result;
    }

    public function getShopCartPositionProperty(int $id) {
        $result = $this->database->select('twarenkorbposeigenschaft', 'kWarenkorbPosEigenschaft', $id);
        return empty($result) ? null : $result;
    }

    public function getShopCartPositionProperties(array $cartPositions): array {
        $result = [];
        foreach($cartPositions as $cartPosition) {
            $properties = $this->database->selectAll('twarenkorbposeigenschaft', 'kWarenkorbPos', (int) $cartPosition->kWarenkorbPos);
            if(!empty($properties)) {
                foreach($properties as $property) {
                    $result[] = $property;
                }
            }
        }
        return $result;
    }

    public function getShopBillingAddress(int $id) {
        $result = $this->database->select('trechnungsadresse', 'kRechnungsadresse', $id);
        return empty($result) ? null : $result;
    }

    public function getShopDeliveryAddress(int $id) {
        $result = $this->database->select('tlieferadresse', 'kLieferadresse', $id);
        return empty($result) ? null : $result;
    }

    public function getShopPaymentMethod(int $id) {
        $result = $this->database->select('tzahlungsart', 'kZahlungsart', $id);
        return empty($result) ? null : $result;
    }

    public function getShopShippingMethod(int $id) {
        $result = $this->database->select('tversandart', 'kVersandart', $id);
        return empty($result) ? null : $result;
    }

    public function getShopOrderAttributes(int $id) {
        $result = $this->database->selectAll('tbestellattribut', 'kBestellung', $id);
        return empty($result) ? [] : $result;
    }

    public function insertNewCart($newCart) {
        $newId = $this->database->insert('twarenkorb', $newCart);
        return $this->getShopCart($newId);
    }

    public function insertNewCartPosition($newCartPosition) {
        $newId = $this->database->insert('twarenkorbpos', $newCartPosition);
        return $this->getShopCartPosition($newId);
    }

    public function insertNewCartPositionProperty($newCartPositionProperty) {
        $newId = $this->database->insert('twarenkorbposeigenschaft', $newCartPositionProperty);
        return $this->getShopCartPositionProperty($newId);
    }

    public function insertNewBillingAddress($newBillingAddress) {
        $newId = $this->database->insert('trechnungsadresse', $newBillingAddress);
        return $this->getShopBillingAddress($newId);
    }

    public function insertNewOrder($newOrder) {
        $newId = $this->database->insert('tbestellung', $newOrder);
        // Also insert tbestellid and tbestellstatus for good measure
        $this->database->insert('tbestellid', (object) [
            'cId' => uniqid('', true),
            'kBestellung' => $newId,
            'dDatum' => 'NOW()'
        ]);
        $this->database->insert('tbestellstatus', (object) [
            'kBestellung' => $newId,
            'dDatum' => 'NOW()',
            'cUID' => uniqid('', true)
        ]);
        return $this->getShopOrder($newId);
    }

    public function getPaymentMethodByModuleId($moduleId) {
        $result = $this->database->select('tzahlungsart', 'cModulId', $moduleId);
        return empty($result) ? null : $result;
    }

    public function insertNewOrderAttribute($newOrderAttribute) {
        $newId = $this->database->insert('tbestellattribut', $newOrderAttribute);
        return $this->getShopOrderAttribute($newId);
    }

    public function loadSubscriptions(int $offset, int $limit) {
        $sql = 'SELECT * FROM ' . self::PLUGIN_TABLE_NAME_SUBSCRIPTION . ' ORDER BY shopOrderId DESC LIMIT :limit OFFSET :offset';
        $subscriptions = $this->database->executeQueryPrepared($sql, ['limit' => $limit, 'offset' => $offset], ReturnType::ARRAY_OF_OBJECTS);
        if (empty($subscriptions)) {
            return [];
        }
        foreach($subscriptions as &$subscription) {
            $orders = $this->getOrdersForSubscription((int)$subscription->id);
            foreach($orders as &$order) {
                if((int) $order->kBestellung === (int)$subscription->shopOrderId) {
                    $subscription->shopOrderNumber = $order->cBestellNr;
                }
                $order->charges = $this->database->executeQueryPrepared('SELECT * FROM ' .self::PLUGIN_TABLE_NAME_CHARGE . ' WHERE shopOrderId = :shopOrderId', ['shopOrderId' => (int) $order->kBestellung], ReturnType::ARRAY_OF_OBJECTS);
                if(empty($order->charges)) {
                    $order->charges = [];
                }
            }
            if((int) $subscription->jtlCustomerId > 0) {
                $subscription->customer = $this->database->select('tkunde', 'kKunde', (int) $subscription->jtlCustomerId);
            }
            $subscription->orders = $orders;
        }
        return $subscriptions;
    }

    public function deleteInvoiceAddress(int $param): void {
        $this->database->delete('trechnungsadresse', 'kRechnungsadresse', $param);
    }

    public function deleteCartPositionProperty(int $param): void {
        $this->database->delete('twarenkorbposeigenschaft', 'kWarenkorbPosEigenschaft', $param);
    }

    public function deleteCartPosition(int $param): void {
        $this->database->delete('twarenkorbpos', 'kWarenkorbPos', $param);
    }

    public function deleteCart(int $param): void {
        $this->database->delete('twarenkorb', 'kWarenkorb', $param);
    }

    public function deleteCharge($chargeId): void {
        $this->database->delete(self::PLUGIN_TABLE_NAME_CHARGE, 'chargeId', $chargeId);

    }

    public function getSubscriptionsByChargePermissionId(string $chargePermissionId): array {
        $subscriptions = $this->database->selectAll(self::PLUGIN_TABLE_NAME_SUBSCRIPTION, 'chargePermissionId', $chargePermissionId);
        if(empty($subscriptions)) {
            return [];
        }
        $result = [];
        foreach($subscriptions as $subscription) {
            $result[] = new Subscription($subscription);
        }
        return $result;
    }

    public function getSubscriptionsByCustomerId(int $customerId): array {
        $subscriptions = $this->database->selectAll(self::PLUGIN_TABLE_NAME_SUBSCRIPTION, 'jtlCustomerId', $customerId);
        if(empty($subscriptions)) {
            return [];
        }
        $result = [];
        foreach($subscriptions as $subscription) {
            $result[] = new Subscription($subscription);
        }
        return $result;
    }

    public function getOrdersForSubscription(int $subscriptionId) {
        $ordersSql = 'SELECT * FROM ' . self::PLUGIN_TABLE_NAME_SUBSCRIPTION_ORDER . ' LEFT JOIN tbestellung ON tbestellung.kBestellung = ' . self::PLUGIN_TABLE_NAME_SUBSCRIPTION_ORDER . '.shopOrderId WHERE subscriptionId = :subscriptionId ORDER BY shopOrderId DESC';
        $orders = $this->database->executeQueryPrepared($ordersSql, ['subscriptionId' => $subscriptionId], ReturnType::ARRAY_OF_OBJECTS);
        if(empty($orders)) {
            return [];
        }
        return $orders;
    }

    /**
     * Gets active subscriptions that have a next order timestamp between (and including) the lower / upper time limit
     * @param int $lowerTimestampLimit
     * @param int $upperTimestampLimit
     * @return array
     */
    public function getSubscriptionsDueBetween(int $lowerTimestampLimit, int $upperTimestampLimit): array {
        $sql = 'SELECT * FROM ' . self::PLUGIN_TABLE_NAME_SUBSCRIPTION . ' WHERE status = :activeState AND nextOrderTimestamp >= :lowerTimestampLimit AND nextOrderTimestamp <= :upperTimestampLimit';
        $subscriptions = $this->database->queryPrepared($sql, ['activeState' => Subscription::STATUS_ACTIVE, 'lowerTimestampLimit' => $lowerTimestampLimit, 'upperTimestampLimit' => $upperTimestampLimit], ReturnType::ARRAY_OF_OBJECTS);
        if(empty($subscriptions)) {
            return [];
        }
        $result = [];
        foreach($subscriptions as $subscription) {
            $result[] = new Subscription($subscription);
        }
        return $result;
    }

}
