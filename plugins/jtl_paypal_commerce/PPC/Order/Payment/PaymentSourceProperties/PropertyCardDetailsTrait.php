<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\Order\Payment\CardDetails;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Trait PropertyCardDetailsTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyCardDetailsTrait
{
    public function setCard(?CardDetails $cardDetails = null): static
    {
        $this->setMappedValue('card', $cardDetails);

        return $this;
    }

    public function getCard(): ?CardDetails
    {
        $card = $this->getMappedValue('card');
        if (empty($card) || ($card instanceof SerializerInterface && $card->isEmpty())) {
            return null;
        }

        return $card instanceof CardDetails ? $card : new CardDetails($card);
    }
}
