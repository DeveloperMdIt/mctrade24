<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use Illuminate\Support\Collection;

/**
 * Class APM
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class APM
{
    public const CREDIT_CARD     = 'card';        // Credit or debit cards
    public const PAYPAL_CREDIT   = 'credit';      // "PayPal Pay Later" (only USA and UK)
    public const PAYPAL_PAYLATER = 'paylater';    // "PayPal Später bezahlen" (only Germany)
    public const BANCONTACT      = 'bancontact';  // Bancontact
    public const BLIK            = 'blik';        // BLIK
    public const EPS             = 'eps';         // eps
    public const IDEAL           = 'ideal';       // iDEAL
    public const MERCADOPAGO     = 'mercadopago'; // Mercado Pago
    public const MYBANK          = 'mybank';      // MyBank
    public const PRZELEWY24      = 'p24';         // Przelewy24
    public const SEPA            = 'sepa';        // SEPA-Lastschrift
    public const SOFORT          = 'sofort';      // Sofort
    public const VENMO           = 'venmo';       // Venmo
    public const SATISPAY        = 'satispay';    // satispay

    public const APM_CARDS  = [self::CREDIT_CARD];
    public const APM_CREDIT = [self::PAYPAL_CREDIT, self::PAYPAL_PAYLATER];
    public const APM_BANK   = [self::BANCONTACT, self::BLIK, self::EPS, self::IDEAL, self::MERCADOPAGO,
                               self::MYBANK, self::PRZELEWY24, self::SEPA, self::SOFORT, self::VENMO];
    public const APM_AC     = [self::BANCONTACT, self::BLIK, self::EPS, self::IDEAL, self::MYBANK,
                               self::PRZELEWY24, self::SATISPAY, self::SOFORT];
    public const APM_ALL    = [self::BANCONTACT, self::BLIK, self::EPS, self::IDEAL, self::MERCADOPAGO,
                               self::MYBANK, self::PRZELEWY24, self::SEPA, self::SOFORT, self::VENMO,
                               self::CREDIT_CARD, self::PAYPAL_CREDIT, self::PAYPAL_PAYLATER, self::SATISPAY];

    /** @var string[][] https://developer.paypal.com/docs/checkout/payment-methods/ */
    public const APM_COUNTRIES = [
        self::CREDIT_CARD     => ['Alle von PayPal unterstützten Länder'],
        self::PAYPAL_CREDIT   => [
            'Australien', 'Frankreich', 'Großbritannien', 'Italien', 'Spanien', 'Vereinigte Staaten'
        ],
        self::PAYPAL_PAYLATER => ['Deutschland'],
        self::BANCONTACT      => ['Belgien'],
        self::BLIK            => ['Polen'],
        self::EPS             => ['Österreich'],
        self::IDEAL           => ['Niederlande'],
        self::MERCADOPAGO     => ['Brasilien', 'Mexico'],
        self::MYBANK          => ['Italien'],
        self::PRZELEWY24      => ['Polen'],
        self::SEPA            => ['Deutschland'],
        self::SOFORT          => [
            'Österreich', 'Belgien', 'Deutschland', 'Italien', 'Niederlande', 'Spanien', 'Großbritannien'
        ],
        self::VENMO           => ['Vereinigte Staaten'],
        self::SATISPAY        => [
            'Luxemburg', 'Österreich', 'Belgien', 'Frankreich',
            'Deutschland', 'Irland', 'Italien', 'Niederlande', 'Spanien'
        ],
    ];

    /** @var Configuration */
    protected Configuration $config;

    /**
     * APM constructor.
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @param bool $ppcExpress
     * @return string[]
     */
    public function getEnabled(bool $ppcExpress): array
    {
        if ($this->config->getWebhookId() === '') {
            return [];
        }
        $enabled = \explode(',', $this->config->getPrefixedConfigItem('paymentMethods_enabled', ''));

        return $ppcExpress ? \array_intersect(self::APM_CREDIT, $enabled) : $enabled;
    }

    /**
     * @param bool $ppcExpress
     * @return string[]
     */
    public function getDisabled(bool $ppcExpress): array
    {
        if ($this->config->getWebhookId() === '') {
            return self::APM_ALL;
        }
        $enabled = $this->getEnabled($ppcExpress);

        return (new Collection(self::APM_ALL))->filter(static function ($item) use ($enabled) {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            return !\in_array($item, $enabled, false);
        })->toArray();
    }

    /**
     * @param string[] $enabled
     */
    public function setEnabled(array $enabled = self::APM_ALL): void
    {
        $this->config->saveConfigItems(['paymentMethods_enabled' => \implode(',', $enabled)]);
    }

    /**
     * @param string $apm
     * @param bool   $ppcExpress
     * @return bool
     */
    public function isEnabled(string $apm, bool $ppcExpress): bool
    {
        return \in_array($apm, $this->getEnabled($ppcExpress));
    }

    /**
     * @param string $apm
     * @return object|null
     */
    public function getPaymentFields(string $apm): ?object
    {
        return match ($apm) {
            self::BANCONTACT,
            self::IDEAL,
            self::MYBANK => (object)[
                'name' => '{if $Kunde !== null}{$Kunde->cVorname} {$Kunde->cNachname}{/if}'
            ],
            self::BLIK,
            self::PRZELEWY24 => (object)[
                'name'  => '{if $Kunde !== null}{$Kunde->cVorname} {$Kunde->cNachname}{/if}',
                'email' => '{if $Kunde !== null}{$Kunde->cMail}{/if}',
            ],
            self::EPS,
            self::SOFORT => (object)[
                'name'        => '{if $Kunde !== null}{$Kunde->cVorname} {$Kunde->cNachname}{/if}',
                'countryCode' => '{if $Kunde !== null}{$Kunde->cLand}{/if}',
            ],
            default => null,
        };
    }
}
