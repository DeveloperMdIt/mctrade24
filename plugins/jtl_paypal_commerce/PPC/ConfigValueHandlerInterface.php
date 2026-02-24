<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

interface ConfigValueHandlerInterface
{
    public function prepare(string $value): string;

    public function followUp(string $value): string;

    public function getValue(string $value): string;

    public function setValue(string $value): string;
}
