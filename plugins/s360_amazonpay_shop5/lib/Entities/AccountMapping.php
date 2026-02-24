<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\lib\Entities;
/**
 * Class AccountMapping
 * An account mapping between a JTL Shop account and an Amazon User.
 */
class AccountMapping {

    public const DEFAULT_JTL_CUSTOMER_ID = -1;

    /**
     * Technical primary key.
     * @var int $id 
     */
    private $id;

    /**
     * The user id from Amazon.
     * @var string $amazonUserId 
     */
    private $amazonUserId;

    /**
     * The technical key of the mapped jtl user account.
     * @var int $jtlCustomerId 
     */
    private $jtlCustomerId;

    /**
     * Flag if this account is verified or not.
     * @var bool $isVerified 
     */
    private $isVerified;

    public function __construct($databaseResult = null) {
        if($databaseResult !== null) {
            $this->id = (int) $databaseResult->id;
            $this->amazonUserId = (string) $databaseResult->amazonUserId;
            $this->jtlCustomerId = $databaseResult->jtlCustomerId === null ? null : (int) $databaseResult->jtlCustomerId;
            $this->isVerified = (bool) $databaseResult->isVerified;
        } else {
            $this->isVerified = false;
        }
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AccountMapping
     */
    public function setId(int $id): AccountMapping {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmazonUserId(): string {
        return $this->amazonUserId;
    }

    /**
     * @param string $amazonUserId
     * @return AccountMapping
     */
    public function setAmazonUserId(string $amazonUserId): AccountMapping {
        $this->amazonUserId = $amazonUserId;
        return $this;
    }

    /**
     * @return int
     */
    public function getJtlCustomerId(): int {
        return $this->jtlCustomerId;
    }

    /**
     * @param int $jtlCustomerId
     * @return AccountMapping
     */
    public function setJtlCustomerId(int $jtlCustomerId): AccountMapping {
        $this->jtlCustomerId = $jtlCustomerId;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsVerified(): bool {
        return $this->isVerified;
    }

    /**
     * @param boolean $isVerified
     * @return AccountMapping
     */
    public function setIsVerified(bool $isVerified): AccountMapping {
        $this->isVerified = $isVerified;
        return $this;
    }



    public function getDatabaseObject(): \stdClass {
        $result = new \stdClass();
        if(null !== $this->id) {
            // id may be null for new mappings.
            $result->id = $this->id;
        }
        $result->amazonUserId = $this->amazonUserId;
        if(null !== $this->jtlCustomerId) {
            $result->jtlCustomerId = $this->jtlCustomerId;
        }
        if(null !== $this->isVerified) {
            $result->isVerified = $this->isVerified;
        }
        return $result;
    }

}