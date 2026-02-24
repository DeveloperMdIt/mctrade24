<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Exceptions;

/**
 * Class InvalidConfigurationException
 *
 * This exception is thrown whenever an invalid configuration in encountered (i.e. missing client IDs, etc.)
 * Plugin-specific actions should usually be aborted and the error logged so the merchant can fix it.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Exceptions
 */
class InvalidConfigurationException extends \Exception {
    /**
     * Constructor. DO NOT CHANGE OR REMOVE - we need this constructor, to set the code as something other than an int/long.
     *
     * PHP is horrendously stupid when it comes to extensions of Exception (see PDOException vs Exception for further details)
     *
     * @param string $message
     * @param mixed $code
     */
    public function __construct($message = '', $code = 0) {
        $this->message = $message;
        $this->code = $code;
    }
}