<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Interface DatabaseObject
 *
 * Interface for objects that may be saved to the database.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects
 */
interface DatabaseObject {
    public function fillFromDatabaseObject(\stdClass $object);
    public function getDatabaseObject(): \stdClass;
}