<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Entities;

use Plugin\s360_amazonpay_shop5\lib\Utils\Interval;

/**
 * A subscription within the Amazon Pay plugin.
 */
class Subscription {

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_UNKNOWN = 'unknown';

    public const REASON_ACCOUNT_DELETED = 'AccountDeleted';
    public const REASON_CHARGE_PERMISSION_CLOSED = 'ChargePermissionClosed';
    public const REASON_UNRECOVERABLE_EXCEPTION = 'UnrecoverableException';
    public const REASON_CHARGE_PROBLEM = 'ChargeProblem';
    public const REASON_CHARGE_PERMISSION_PROBLEM = 'ChargePermissionProblem';
    public const REASON_PRODUCT_DEACTIVATED = 'ProductDeactivated';
    public const REASON_PRODUCT_DOES_NOT_EXIST = 'ProductDoesNotExist';
    public const REASON_PRODUCT_STOCK_LEVELS = 'ProductStockLevels';
    public const REASON_MERCHANT_PAUSED = 'MerchantPaused';
    public const REASON_MERCHANT_CANCELED = 'MerchantCanceled';

    /**
     * @var int|null $id
     */
    protected $id;

    /**
     * @var int $shopOrderId
     */
    protected $shopOrderId;

    /**
     * @var string $shopOrderNumber
     */
    protected $shopOrderNumber;


    /**
     * @var int $jtlCustomerId
     */
    protected $jtlCustomerId;

    /**
     * @var Interval $interval;
     */
    protected $interval;

    /**
     * @var int $lastOrderTimestamp
     */
    protected $lastOrderTimestamp;

    /**
     * @var int $nextOrderTimestamp
     */
    protected $nextOrderTimestamp;

    /**
     * @var string $chargePermissionId
     */
    protected $chargePermissionId;

    /**
     * @var string $status
     */
    protected $status;

    /**
     * @var string $statusReason
     */
    protected $statusReason;

    public function __construct($stdClassObject = null) {
        if($stdClassObject !== null) {
            $this->id = (int) $stdClassObject->id;
            $this->shopOrderId = (int) $stdClassObject->shopOrderId;
            $this->shopOrderNumber = $stdClassObject->shopOrderNumber;
            $this->jtlCustomerId = (int) $stdClassObject->jtlCustomerId;
            $this->interval = $stdClassObject->interval === null ? null : Interval::fromString($stdClassObject->interval);
            $this->lastOrderTimestamp = (int) $stdClassObject->lastOrderTimestamp;
            $this->nextOrderTimestamp = (int) $stdClassObject->nextOrderTimestamp;
            $this->chargePermissionId = $stdClassObject->chargePermissionId ?? '';
            $this->status = $stdClassObject->status ?? self::STATUS_UNKNOWN;
            $this->statusReason = $stdClassObject->statusReason ?? '';
        } else {
            $this->id = null;
            $this->shopOrderId = null;
            $this->shopOrderNumber = null;
            $this->jtlCustomerId = null;
            $this->interval = null;
            $this->lastOrderTimestamp = 0;
            $this->nextOrderTimestamp = 0;
            $this->chargePermissionId = '';
            $this->status = self::STATUS_UNKNOWN;
            $this->statusReason = '';
        }
    }

    /**
     * @return int
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getShopOrderId(): int {
        return $this->shopOrderId;
    }

    /**
     * @param int $shopOrderId
     */
    public function setShopOrderId(int $shopOrderId): void {
        $this->shopOrderId = $shopOrderId;
    }

    /**
     * @return string
     */
    public function getShopOrderNumber(): ?string {
        return $this->shopOrderNumber;
    }

    /**
     * @param string $shopOrderNumber
     */
    public function setShopOrderNumber(?string $shopOrderNumber): void {
        $this->shopOrderNumber = $shopOrderNumber;
    }

    /**
     * @return int
     */
    public function getJtlCustomerId(): int {
        return $this->jtlCustomerId;
    }

    /**
     * @param int $jtlCustomerId
     */
    public function setJtlCustomerId(int $jtlCustomerId): void {
        $this->jtlCustomerId = $jtlCustomerId;
    }

    /**
     * @return Interval
     */
    public function getInterval(): ?Interval {
        return $this->interval;
    }

    /**
     * @param Interval $interval
     */
    public function setInterval(Interval $interval): void {
        $this->interval = $interval;
    }

    /**
     * @return int
     */
    public function getLastOrderTimestamp(): int {
        return $this->lastOrderTimestamp;
    }

    /**
     * @param int $lastOrderTimestamp
     */
    public function setLastOrderTimestamp(int $lastOrderTimestamp): void {
        $this->lastOrderTimestamp = $lastOrderTimestamp;
    }

    /**
     * @return int
     */
    public function getNextOrderTimestamp(): int {
        return $this->nextOrderTimestamp;
    }

    /**
     * @param int $nextOrderTimestamp
     */
    public function setNextOrderTimestamp(int $nextOrderTimestamp): void {
        $this->nextOrderTimestamp = $nextOrderTimestamp;
    }

    /**
     * @return string
     */
    public function getChargePermissionId(): string {
        return $this->chargePermissionId;
    }

    /**
     * @param string $chargePermissionId
     */
    public function setChargePermissionId(string $chargePermissionId): void {
        $this->chargePermissionId = $chargePermissionId;
    }

    /**
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatusReason(): string {
        return $this->statusReason;
    }

    /**
     * @param string $statusReason
     */
    public function setStatusReason(string $statusReason): void {
        $this->statusReason = $statusReason;
    }

    public function getDatabaseObject(): \stdClass {
        $result = new \stdClass();
        if(null !== $this->id) {
            // id may be null for new objects.
            $result->id = $this->id;
        }
        $result->shopOrderId = $this->shopOrderId;
        $result->shopOrderNumber = $this->shopOrderNumber;
        $result->jtlCustomerId = $this->jtlCustomerId;
        $result->interval = $this->interval->toString();
        $result->lastOrderTimestamp = $this->lastOrderTimestamp;
        $result->nextOrderTimestamp = $this->nextOrderTimestamp;
        $result->chargePermissionId = $this->chargePermissionId;
        $result->status = $this->status;
        $result->statusReason = $this->statusReason;
        return $result;
    }

}