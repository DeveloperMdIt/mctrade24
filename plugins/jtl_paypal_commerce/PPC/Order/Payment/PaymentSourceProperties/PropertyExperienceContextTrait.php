<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Trait PropertyExperienceContextTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyExperienceContextTrait
{
    public function setExperienceContext(?ExperienceContext $experienceContext = null): static
    {
        $this->setMappedValue('experience_context', $experienceContext);

        return $this;
    }

    public function getExperienceContext(): ?ExperienceContext
    {
        $expContext = $this->getMappedValue('experience_context');
        if (empty($expContext) || ($expContext instanceof SerializerInterface && $expContext->isEmpty())) {
            return null;
        }

        return $expContext instanceof ExperienceContext ? $expContext : $this->createExperienceContext($expContext);
    }

    protected function initdataExperienceContext(object $data): void
    {
        if (!($data instanceof ExperienceContext)) {
            $this->data->experience_context = $this->createExperienceContext($data);
        }
    }
}
