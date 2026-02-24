<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

/**
 * Class CryptedTagConfigValueHandler
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class CryptedTagConfigValueHandler extends CryptedConfigValueHandler
{
    private const CRC_TAG = '$CRC$_';

    private function hasTag(string $value): bool
    {
        return \str_starts_with($value, self::CRC_TAG);
    }

    public function prepare(string $value): string
    {
        return $this->hasTag($value) ? \substr($value, \strlen(self::CRC_TAG)) : $value;
    }

    public function followUp(string $value): string
    {
        return $this->hasTag($value) ? $value : self::CRC_TAG . $value;
    }
}
