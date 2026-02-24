<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Exception;
use JTL\Shop;

/**
 * Class Transaction
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Transaction
{
    /** @var self */
    private static self $instance;

    /** @var string[] */
    private array $transactionId;

    /** @var int */
    private const ID_LENGTH = 16;

    /** @var string */
    private const SESSION_IDENTIFIER = 'PPC:Transaction';

    public const CONTEXT_CREATE  = 1;
    public const CONTEXT_CAPTURE = 2;

    /**
     * Transaction constructor
     */
    private function __construct()
    {
        $this->transactionId = $_SESSION[self::SESSION_IDENTIFIER] ?? [];

        self::$instance = $this;
    }

    /**
     * @return static
     */
    public static function instance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @param int $context
     */
    private function resetTransactions(int $context): void
    {
        foreach (\array_keys($this->transactionId) as $key) {
            if ($key < $context) {
                unset($this->transactionId[$key]);
            }
        }
    }

    /**
     * @param int $context
     * @return string
     */
    public function startTransaction(int $context): string
    {
        $this->resetTransactions($context);

        if (($this->transactionId[$context] ?? null) === null) {
            try {
                $this->transactionId[$context] = Shop::Container()->getCryptoService()->randomString(self::ID_LENGTH);

                $_SESSION[self::SESSION_IDENTIFIER] = $this->transactionId;
            } catch (Exception $e) {
                try {
                    Shop::Container()->getLogService()->error('Transaction::startTransaction - cant start transaction ('
                        . $e->getMessage() . ')');
                    throw new TransactionException('Transaction can not be started.', $e->getCode(), $e);
                } catch (Exception) {
                    // nothing to do...
                }
            }
        }

        return $this->transactionId[$context];
    }

    /**
     * @param int $context
     * @return string
     */
    public function getTransactionId(int $context): string
    {
        if (($this->transactionId[$context] ?? null) === null) {
            throw new TransactionException('Transaction has not yet started.');
        }

        return $this->transactionId[$context];
    }

    /**
     * @param int $context
     * @return void
     */
    public function clearTransaction(int $context): void
    {
        unset($this->transactionId[$context]);
        $_SESSION[self::SESSION_IDENTIFIER] = $this->transactionId;
    }

    /**
     * @return void
     */
    public function clearAllTransactions(): void
    {
        $this->transactionId = [];
        unset($_SESSION[self::SESSION_IDENTIFIER]);
    }
}
