<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use InvalidArgumentException;

/**
 * Class PaymentSourceBuilder
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class PaymentSourceBuilder
{
    public const FUNDING_CARD       = 'card';
    public const FUNDING_TOKEN      = 'token';
    public const FUNDING_PAYPAL     = 'paypal';
    public const FUNDING_BANCONTACT = 'bancontact';
    public const FUNDING_BLIK       = 'blik';
    public const FUNDING_EPS        = 'eps';
    public const FUNDING_GIROPAY    = 'giropay';
    public const FUNDING_IDEAL      = 'ideal';
    public const FUNDING_MYBANK     = 'mybank';
    public const FUNDING_P24        = 'p24';
    public const FUNDING_SOFORT     = 'sofort';
    public const FUNDING_TRUSTLY    = 'trustly';
    public const FUNDING_APPLEPAY   = 'apple_pay';
    public const FUNDING_GOOGLEPAY  = 'google_pay';
    public const FUNDING_VENMO      = 'venmo';
    public const FUNDING_PUI        = 'pay_upon_invoice';

    public const FUNDING_ALL = [
        self::FUNDING_CARD,
        self::FUNDING_TOKEN,
        self::FUNDING_PAYPAL,
        self::FUNDING_BANCONTACT,
        self::FUNDING_BLIK,
        self::FUNDING_EPS,
        self::FUNDING_GIROPAY,
        self::FUNDING_IDEAL,
        self::FUNDING_MYBANK,
        self::FUNDING_P24,
        self::FUNDING_SOFORT,
        self::FUNDING_TRUSTLY,
        self::FUNDING_APPLEPAY,
        self::FUNDING_GOOGLEPAY,
        self::FUNDING_VENMO,
        self::FUNDING_PUI,
    ];

    private string $fundingSource;

    private ?object $data = null;

    /**
     * PaymentSourceBuilder constructor
     */
    public function __construct(string $fundingSource)
    {
        if (!self::isValidPaymentSource($fundingSource)) {
            throw new InvalidArgumentException('Invalid funding source: ' . $fundingSource);
        }

        $this->fundingSource = $fundingSource;
    }

    public function setData(?object $data = null): self
    {
        $this->data = $data;

        return $this;
    }

    public static function isValidPaymentSource(string $fundingSource): bool
    {
        return \in_array($fundingSource, self::FUNDING_ALL);
    }

    public function build(): PaymentSourceInterface
    {
        return match ($this->fundingSource) {
            self::FUNDING_CARD => new CardPaymentSource($this->data),
            self::FUNDING_TOKEN => new TokenPaymentSource($this->data),
            self::FUNDING_PAYPAL => new PayPalPaymentSource($this->data),
            self::FUNDING_BANCONTACT => new BancontactPaymentSource($this->data),
            self::FUNDING_BLIK => new BlikPaymentSource($this->data),
            self::FUNDING_EPS => new EpsPaymentSource($this->data),
            self::FUNDING_GIROPAY => new GiropayPaymentSource($this->data),
            self::FUNDING_IDEAL => new IDealPaymentSource($this->data),
            self::FUNDING_MYBANK => new MyBankPaymentSource($this->data),
            self::FUNDING_P24 => new P24PaymentSource($this->data),
            self::FUNDING_SOFORT => new SofortPaymentSource($this->data),
            self::FUNDING_TRUSTLY => new TrustlyPaymentSource($this->data),
            self::FUNDING_APPLEPAY => new ApplePayPaymentSource($this->data),
            self::FUNDING_GOOGLEPAY => new GooglePayPaymentSource($this->data),
            self::FUNDING_VENMO => new VenmoPaymentSource($this->data),
            self::FUNDING_PUI => new PUIPaymentSource($this->data),
            default => throw new InvalidArgumentException('Invalid funding source: ' . $this->fundingSource),
        };
    }
}
