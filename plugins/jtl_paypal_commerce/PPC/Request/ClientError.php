<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

use function Functional\first;

/**
 * Class ClientError
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class ClientError extends JSON
{
    /**
     * ClientError constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @return string
     */
    public function getDebugId(): string
    {
        return $this->getData()->debug_id ?? '';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData()->name ?? 'Unknown client error';
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->getData()->message ?? 'Unspecified error message';
    }

    /**
     * @return ClientErrorDetail[]
     */
    public function getDetails(): array
    {
        return \array_map(static function (object $item) {
            return new ClientErrorDetail($item);
        }, $this->getData()->details ?? []);
    }

    /**
     * @param int $pos
     * @return object|null
     */
    public function getDetail(int $pos = 0): ?ClientErrorDetail
    {
        $details = $this->getDetails();

        return $details[$pos] ?? null;
    }

    /**
     * @param string $rel
     * @return string|null
     */
    public function getLink(string $rel): ?string
    {
        $link = first($this->getData()->links ?? [], static function (object $item) use ($rel) {
            return $item->rel === $rel;
        });

        return $link !== null ? $link->href : null;
    }
}
