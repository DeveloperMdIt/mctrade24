<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

/**
 * Class FormRules
 * @package Plugin\jtl_search
 */
class FormRules
{
    /**
     * @param string $value
     * @param mixed  $optional
     * @return bool
     */
    public function base64decodeable($value, $optional): bool
    {
        return \count(\explode(':::', \base64_decode($value))) === 2;
    }

    /**
     * @param string $value
     * @param mixed  $optional
     * @return bool
     */
    public function required($value, $optional): bool
    {
        return (\is_numeric($value) || (\is_string($value) && \strlen($value) > 0));
    }

    /**
     * @param string $value
     * @param int    $minLength
     * @return bool
     */
    public function minlength($value, $minLength): bool
    {
        return \is_string($value) && \strlen($value) >= (int)$minLength;
    }

    /**
     * @param string $value
     * @param int    $maxLength
     * @return bool
     */
    public function maxlength($value, $maxLength): bool
    {
        return \is_string($value) && \strlen($value) <= (int)$maxLength;
    }

    /**
     * @param string $value
     * @param mixed  $optional
     * @return bool
     */
    public function email($value, $optional): bool
    {
        foreach ([',', ' '] as $item) {
            if (\strpos($value, $item)) {
                return false;
            }
        }
        if (\preg_match('/[a-z0-9_+-]+(\.[a-z0-9_+-]+)*@[a-z0-9öüä-]+(\.[a-z0-9öüä-]+)*\.([a-z]{2,4})$/ui', $value)) {
            return true;
        }

        return false;
    }
}
