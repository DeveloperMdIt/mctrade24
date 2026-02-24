<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class DeliveryTrackersPayload
 *
 * Payload class for Delivery Notification.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects
 */
class DeliveryTrackersPayload  extends AbstractObject {

    /**
     * @var string $amazonOrderReferenceId
     */
    protected $amazonOrderReferenceId;

    /**
     * @var string $chargePermissionId
     */
    protected $chargePermissionId;

    /**
     * @var DeliveryDetail[] $deliveryDetails
     */
    protected $deliveryDetails;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->amazonOrderReferenceId = $data['amazonOrderReferenceId'] ?? null;
        $this->chargePermissionId = $data['chargePermissionId'] ?? null;
        if (isset($data['deliveryDetails']) && \is_array($data['deliveryDetails'])) {
            $this->deliveryDetails = [];
            foreach ($data['deliveryDetails'] as $deliveryDetail) {
                if (\is_array($deliveryDetail)) {
                    $this->deliveryDetails[] = new DeliveryDetail($deliveryDetail);
                }
            }
        }
    }

    public function toArray() {
        $deliveryDetailsArray = [];
        if(null !== $this->deliveryDetails) {
            foreach ($this->deliveryDetails as $deliveryDetail) {
                $deliveryDetailsArray[] = $deliveryDetail->toArray();
            }
        }
        $result = [];
        if($this->amazonOrderReferenceId !== null) {
            $result['amazonOrderReferenceId'] = $this->amazonOrderReferenceId;
        } elseif($this->chargePermissionId !== null) {
            $result['chargePermissionId'] = $this->chargePermissionId;
        }
        $result['deliveryDetails'] = $deliveryDetailsArray;
        return $result;
    }

    public function setDeliveryDetails($deliveryDetails) {
        $this->deliveryDetails = $deliveryDetails;
    }

}