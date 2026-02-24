<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

/**
 * Class PaymentSession
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
final class PaymentSession
{
    public const BNCODE  = 'ppcBNCode';
    public const FUNDING = 'ppcFundingSource';
    public const ORDERID = 'ppcOrderId';
    public const HASH    = 'orderHash';

    /** @var self[] */
    private static array $instance = [];

    /** @var string */
    private string $moduleID;

    /**
     * PaymentSession constructor
     * @param string $moduleID
     */
    private function __construct(string $moduleID)
    {
        $this->moduleID            = $moduleID;
        self::$instance[$moduleID] = $this;
    }

    /**
     * @param string $moduleID
     * @return self
     */
    public static function instance(string $moduleID): self
    {
        return self::$instance[$moduleID] ?? new self($moduleID);
    }

    /**
     * @param string     $varName
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $varName, mixed $default = null): mixed
    {
        return $_SESSION[$this->moduleID][$varName] ?? $default;
    }

    /**
     * @param string $varName
     * @param mixed  $value
     * @return self
     */
    public function set(string $varName, mixed $value): self
    {
        $_SESSION[$this->moduleID][$varName] = $value;

        return $this;
    }

    /**
     * @param string $varName
     * @param int    $default
     * @return int
     */
    public function getInt(string $varName, int $default = 0): int
    {
        return (int)($this->get($varName) ?? $default);
    }

    /**
     * @param string|null $varName
     * @return self
     */
    public function clear(?string $varName = null): self
    {
        if ($varName === null) {
            unset($_SESSION[$this->moduleID]);
        } else {
            unset($_SESSION[$this->moduleID][$varName]);
        }

        return $this;
    }

    public function clearPayment(): self
    {
        foreach (
            [
                self::BNCODE,
                self::FUNDING,
                self::ORDERID,
                self::HASH,
            ] as $varName
        ) {
            $this->clear($varName);
        }

        return $this;
    }

    /**
     * @param string|null $bnCode
     * @return string|null
     */
    public function getBNCode(?string $bnCode = null): ?string
    {
        return (string)($this->get(self::BNCODE) ?? $bnCode);
    }

    /**
     * @param string $value
     * @return self
     */
    public function setBNCode(string $value): self
    {
        return $this->set(self::BNCODE, $value);
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->get(self::ORDERID);
    }

    /**
     * @param string $orderId
     * @return self
     */
    public function setOrderId(string $orderId): self
    {
        return $this->set(self::ORDERID, $orderId);
    }

    /**
     * @param string|null $hash
     * @return string|null
     */
    public function getOrderHash(?string $hash = null): ?string
    {
        return $this->get(self::HASH) ?? $hash;
    }

    /**
     * @param string $orderHash
     * @return self
     */
    public function setOrderHash(string $orderHash): self
    {
        return $this->set(self::HASH, $orderHash);
    }

    /**
     * @return string|null
     */
    public function getFundingSource(): ?string
    {
        return $this->get(self::FUNDING);
    }

    /**
     * @param string $fundingSource
     * @return self
     */
    public function setFundingSource(string $fundingSource): self
    {
        return $this->set(self::FUNDING, $fundingSource);
    }
}
