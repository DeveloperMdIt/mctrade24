<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use Exception;
use JTL\Alert\Alert;
use Throwable;

/**
 * Class InvalidPayerDataException
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class InvalidPayerDataException extends Exception
{
    /** @var string */
    private string $redirectURL;

    /** @var Alert[] */
    private array $alerts;

    /**
     * @param string         $message
     * @param string         $redirectURL
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', string $redirectURL = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->redirectURL = $redirectURL;
        $this->alerts      = [];
    }

    /**
     * @return bool
     */
    public function hasRedirectURL(): bool
    {
        return $this->redirectURL !== '';
    }

    /**
     * @param string $redirectURL
     * @return self
     */
    public function setRedirectURL(string $redirectURL): self
    {
        $this->redirectURL = $redirectURL;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectURL(): string
    {
        return $this->redirectURL;
    }

    /**
     * @return bool
     */
    public function hasAlerts(): bool
    {
        return \count($this->alerts) > 0;
    }

    /**
     * @return Alert|null
     */
    public function getAlert(): ?Alert
    {
        return $this->alerts[0] ?? null;
    }

    /**
     * @param Alert|null $alert
     * @return InvalidPayerDataException
     */
    public function setAlert(?Alert $alert): self
    {
        $this->alerts[0] = $alert;

        return $this;
    }

    /**
     * @param Alert $alert
     * @return $this
     */
    public function addAlert(Alert $alert): self
    {
        $this->alerts[] = $alert;

        return $this;
    }

    /**
     * @return Alert[]
     */
    public function getAlerts(): array
    {
        return $this->alerts;
    }
}
