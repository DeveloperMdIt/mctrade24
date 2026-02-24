<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JTL\Checkout\Adresse;
use JTL\Checkout\Bestellung;
use JTL\DB\DbInterface;
use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Shipping;
use Plugin\jtl_paypal_commerce\PPC\Order\Vaulting\PaymentTokenGetRequest;
use Plugin\jtl_paypal_commerce\PPC\Order\Vaulting\PaymentTokenResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\Repositories\VaultingRepository;

/**
 * Class Vaulting
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class VaultingHelper
{
    private readonly VaultingRepository $repository;

    private readonly ConfigValueHandlerInterface $cryptedValueHandler;

    private Bestellung $shopOrder;

    private Configuration $config;

    private static ?bool $vaultingEnabled = null;

    private const VAULTED_PAYMENTS = [
        PaymentSourceBuilder::FUNDING_PAYPAL,
    ];

    /**
     * VaultingHelper constructor
     */
    public function __construct(
        Configuration $config,
        ?Bestellung $shopOrder = null,
        ?DbInterface $db = null,
        ?VaultingRepository $repository = null,
        ?CryptoServiceInterface $cryptoService = null
    ) {
        $this->repository          = $repository ?? new VaultingRepository($db ?? Shop::Container()->getDB());
        $this->cryptedValueHandler = new CryptedTagConfigValueHandler(
            $cryptoService ?? Shop::Container()->getCryptoService()
        );
        $this->shopOrder           = $shopOrder ?? new Bestellung();
        $this->config              = $config;
    }

    public static function buildShippingHashFromAdress(Address $address): string
    {
        try {
            $adrObject = \json_decode((string)$address, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '';
        }
        ksort($adrObject, SORT_STRING);

        try {
            return sha1(\json_encode($adrObject, JSON_THROW_ON_ERROR));
        } catch (\JsonException) {
            return '';
        }
    }

    public function buildShippingHash(): string
    {
        $shippingId = (int)$this->shopOrder->kLieferadresse;
        $isShipping = true;
        if ($shippingId === 0) {
            $shippingId = (int)$this->shopOrder->kRechnungsadresse;
            $isShipping = false;
        }
        $shippingAddress = $this->repository->getAddress($shippingId, $isShipping);

        return $shippingAddress === null ? '' : self::buildShippingHashFromAdress(
            Address::createFromOrderAddress((new Adresse())->fromObject($shippingAddress)->decrypt())
        );
    }

    public function storeVault(string $source, JSON $vaultData): void
    {
        $vaultState    = $vaultData->getData()->status ?? '';
        $vaultId       = $vaultData->getData()->id ?? null;
        $vaultCustomer = $vaultData->getData()->customer ?? null;
        $vaultActive   = $vaultId !== null && $vaultCustomer !== null && ($vaultCustomer->id ?? null) !== null;
        if (
            (int)$this->shopOrder->kKunde === 0
            || !$vaultActive
            || !\in_array($vaultState, ['VAULTED', 'APPROVED'])
        ) {
            return;
        }

        $data = (object)[
            'customer_id'         => $this->shopOrder->kKunde,
            'payment_id'          => $this->shopOrder->kZahlungsart,
            'funding_source'      => $source,
            'vault_id'            => $this->cryptedValueHandler->setValue($vaultId ?? ''),
            'vault_id_hash'       => \sha1($vaultId ?? ''),
            'vault_status'        => $vaultState,
            'vault_customer'      => $this->cryptedValueHandler->setValue($vaultCustomer->id),
            'shipping_hash'       => $this->buildShippingHash(),
        ];
        $this->repository->updateOrInsert(
            $data,
            ['id', 'customer_id', 'payment_id', 'funding_source', 'shipping_hash']
        );
    }

    public function deleteVault(string $vaultId): void
    {
        $this->repository->delete(\sha1($vaultId));
    }

    public function isVaultingEnabled(string $fundingSource, ?int $customerId = null, bool $force = false): bool
    {
        if ($customerId === 0 || !\in_array($fundingSource, self::VAULTED_PAYMENTS, true)) {
            return false;
        }

        if ($force || static::$vaultingEnabled === null) {
            static::$vaultingEnabled =
                ($this->config->getPrefixedConfigItem('vaultingDisplay_activateVaulting', 'N') === 'Y')
                && (int)$this->config->getPrefixedConfigItem('PaymentVaultingAvail', '0') > 0;
        }

        return static::$vaultingEnabled;
    }

    private function getVault(int $customerId, int $paymentId, string $fundingSource): ?object
    {
        $mustMigrate = false;
        $vault       = $this->repository->get($customerId, $paymentId, $fundingSource);
        if ($vault === null) {
            return null;
        }

        if (\strcmp($vault->vault_id ?? '', $this->cryptedValueHandler->prepare($vault->vault_id ?? '')) !== 0) {
            $vault->vault_id = $this->cryptedValueHandler->getValue($vault->vault_id ?? '');
        } else {
            $mustMigrate = true;
        }
        if (\strcmp($vault->vault_customer, $this->cryptedValueHandler->prepare($vault->vault_customer)) !== 0) {
            $vault->vault_customer = $this->cryptedValueHandler->getValue($vault->vault_customer);
        } else {
            $mustMigrate = true;
        }
        if ($mustMigrate) {
            $vaultData = (object)[
                'vault_id'            => $this->cryptedValueHandler->setValue($vault->vault_id ?? ''),
                'vault_id_hash'       => \sha1($vault->vault_id ?? ''),
                'vault_customer'      => $this->cryptedValueHandler->setValue($vault->vault_customer),
            ];
            $this->repository->updateOrInsert(
                $vaultData,
                ['id', 'customer_id', 'payment_id', 'funding_source', 'shipping_hash', 'vault_status']
            );
        }

        return $vault;
    }

    public function enableVaulting(string $fundingSource, PayPalPaymentInterface $paymentMethod, bool $enable): bool
    {
        $vaultingEnabled = $this->isVaultingEnabled($fundingSource);
        if ($vaultingEnabled && $enable) {
            $paymentMethod->addCache('ppc_vaulting_enable', 'Y');

            return true;
        }

        $paymentMethod->unsetCache('ppc_vaulting_enable');

        return false;
    }

    public function disableVaultingTemporary(PayPalPaymentInterface $payment): void
    {
        $payment->addCache('ppc_vaulting_disable', 'Y');
    }

    public function isVaultingActive(int $customerId, PayPalPaymentInterface $payment): bool
    {
        if ($customerId === 0) {
            return false;
        }

        return $this->getVault(
            $customerId,
            $payment->getMethod()->getMethodID(),
            $payment->getDefaultFundingSource()
        ) !== null;
    }

    public function getValidCustomerVault(
        int $customerId,
        PayPalPaymentInterface $payment,
        string $shippingHash
    ): ?string {
        if ($customerId === 0 || $payment->getCache('ppc_vaulting_disable') === 'Y') {
            return null;
        }

        $vault          = $this->getVault(
            $customerId,
            $payment->getMethod()->getMethodID(),
            $payment->getDefaultFundingSource()
        );
        $vaultId        = $vault->vault_id ?? null;
        $shippingSecure = ((defined('PPC_DEBUG') && \PPC_DEBUG === true))
            || (($vault->shipping_hash ?? '') === $shippingHash && $shippingHash !== '');
        if ($vault === null || $vaultId === null || !$shippingSecure) {
            return null;
        }

        return $vault->vault_customer;
    }

    public function getShippingAddress(int $customerId, PayPalPaymentInterface $payment): ?Address
    {
        if (!$this->isVaultingActive($customerId, $payment)) {
            return null;
        }

        $vault   = $this->getVault(
            $customerId,
            $payment->getMethod()->getMethodID(),
            $payment->getDefaultFundingSource()
        );
        $vaultId = $vault === null ? null : $vault->vault_id;
        if ($vaultId === null || $vault->vault_status !== 'VAULTED') {
            return null;
        }

        $client = new PPCClient(PPCHelper::getEnvironment());
        try {
            $paymentTokenResponse = new PaymentTokenResponse($client->send(
                new PaymentTokenGetRequest(Token::getInstance()->getToken(), $vaultId)
            ));
        } catch (GuzzleException | Exception) {
            return null;
        }

        $paymentSource = $paymentTokenResponse->getPaymentToken()
                                              ->getPaymentSource($payment->getDefaultFundingSource());
        $shipping      = $paymentSource === null ? null : new Shipping($paymentSource->getProperty('shipping'));

        return $shipping === null ? null : $shipping->getAddress();
    }
}
