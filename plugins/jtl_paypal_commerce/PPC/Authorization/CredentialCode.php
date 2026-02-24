<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use DateTime;
use Exception;
use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Random\RandomException;

/**
 * Class CredentialNonce
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class CredentialCode extends JSON
{
    /**
     * @inheritDoc
     */
    public function __construct(string|object|null $data = null)
    {
        parent::__construct($data);
    }

    public static function create(DateTime $expire, ?string &$code, int $length = 8): self
    {
        $instance = new self();
        $code     = $instance->reset($length);
        $instance->setExpire($expire);

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function setData(object|array|string $data): static
    {
        if (\is_string($data)) {
            try {
                $data = \json_decode($data, false, 512, \JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $this->data = null;

                return $this;
            }
        }

        if (\is_object($data)) {
            $this->data = (object)[
                'hash'   => $data->hash ?? null,
                'expire' => null,
            ];
            try {
                $this->setExpire(\is_string($data->expire) ? new DateTime($data->expire) : null);
            } catch (Exception) {
                $this->data->expire = null;
            }
        } else {
            $this->data = null;
        }

        return $this;
    }

    private function hash(string $code): string
    {
        return \hash('sha256', $code);
    }

    public function reset(int $length = 8): string
    {
        try {
            $code = (string)\random_int(0, 10 ** $length - 1);
        } catch (RandomException) {
            /** @noinspection RandomApiMigrationInspection */
            $code = (string)\mt_rand(0, 10 ** $length - 1);
        }
        $this->data = (object)[
            'hash'   => $this->hash($code),
            'expire' => null,
        ];

        return $code;
    }

    public function verifyCode(string $code): bool
    {
        return \hash_equals($this->data->hash ?? '', $this->hash($code));
    }

    public function setExpire(?DateTime $dateTime = null): self
    {
        if ($this->data === null) {
            return $this;
        }

        $this->data->expire = $dateTime;

        return $this;
    }

    public function isExpired(): bool
    {
        return ($this->data->expire ?? null) === null || $this->data->expire <= (new DateTime());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        return (object)[
            'hash'   => $this->getData()->hash,
            'expire' => ($this->getData()->expire ?? new DateTime())->format('Y-m-d H:i:s'),
        ];
    }
}
