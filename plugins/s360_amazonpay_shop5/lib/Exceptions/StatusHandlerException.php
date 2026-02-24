<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Exceptions;

/**
 * Class StatusHandlerException
 *
 * Exceptions thrown by the status handler.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Exceptions
 */
class StatusHandlerException extends \Exception {
    public const CODE_UNKNOWN_OBJECT = 'UnknownObject';
    public const CODE_UNEXPECTED_STATUS = 'UnexpectedStatus';
    public const CODE_GENERIC = 'Generic';

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