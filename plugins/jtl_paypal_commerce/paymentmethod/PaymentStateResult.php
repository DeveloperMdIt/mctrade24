<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

/**
 * Class PaymentStateResult
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class PaymentStateResult
{
    /** @var string|null */
    private ?string $state;

    /** @var string|null */
    private ?string $redirect;

    /** @var string|null */
    private ?string $pendingMessage;

    /** @var string|null */
    private ?string $completeMessage;

    /** @var bool */
    private bool $timeout;

    /**
     * PaymentStateResult constructor
     * @param string|null $state
     * @param string|null $redirect
     * @param string|null $pendingMessage
     * @param bool        $timeout
     */
    public function __construct(
        ?string $state = null,
        ?string $redirect = null,
        ?string $pendingMessage = null,
        bool $timeout = false
    ) {
        $this->state           = $state;
        $this->redirect        = $redirect;
        $this->pendingMessage  = $pendingMessage;
        $this->timeout         = $timeout;
        $this->completeMessage = null;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     * @return PaymentStateResult
     */
    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRedirect(): bool
    {
        return !empty($this->redirect);
    }

    /**
     * @return string|null
     */
    public function getRedirect(): ?string
    {
        return $this->redirect;
    }

    /**
     * @param string|null $redirect
     * @return PaymentStateResult
     */
    public function setRedirect(?string $redirect): self
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPendingMessage(): bool
    {
        return !empty($this->pendingMessage);
    }

    /**
     * @return string|null
     */
    public function getPendingMessage(): ?string
    {
        return $this->pendingMessage;
    }

    /**
     * @param string|null $pendingMessage
     * @return PaymentStateResult
     */
    public function setPendingMessage(?string $pendingMessage): self
    {
        $this->pendingMessage = $pendingMessage;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCompleteMessage(): bool
    {
        return !empty($this->completeMessage);
    }

    /**
     * @return string|null
     */
    public function getCompleteMessage(): ?string
    {
        return $this->completeMessage;
    }

    /**
     * @param string|null $completeMessage
     * @return PaymentStateResult
     */
    public function setCompleteMessage(?string $completeMessage): self
    {
        $this->completeMessage = $completeMessage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTimeout(): bool
    {
        return $this->timeout;
    }

    /**
     * @param bool $timeout
     * @return PaymentStateResult
     */
    public function setTimeout(bool $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }
}
