<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Adapter;


require_once __DIR__ . '/SnsMessageValidator/vendor/autoload.php';

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;


/**
 * Class IpnAdapter
 *
 * This adapter is used to handle IPN specific things, e.g. validation.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Adapter
 */
class IpnAdapter {


    public static function validateIpnPost($requestBody): bool {
        // Make sure the SNS-provided header exists.
        if (!isset($_SERVER['HTTP_X_AMZ_SNS_MESSAGE_TYPE'])) {
            return false;
        }
        try {
            $message = Message::fromJsonString($requestBody);
            // Validate the message
            $validator = new MessageValidator();
            return $validator->isValid($message);
        } catch(\Exception $e) {
            return false;
        }
    }
}