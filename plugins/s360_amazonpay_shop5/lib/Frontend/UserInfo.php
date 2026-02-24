<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Frontend;

/**
 * Class UserInfo
 *
 * User-Info acquired from a profile via oAuth2.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Frontend
 */
class UserInfo {
    /**
     * The Amazon User Id.
     * @var string $userId 
     */
    private $userId;

    /**
     * The full name of the customer.
     * @var string $name 
     */
    private $name;

    /**
     * The email address of the customer.
     * @var string $email 
     */
    private $email;

    /**
     * @return string
     */
    public function getUserId(): string {
        return $this->userId;
    }

    /**
     * @param string $userId
     * @return UserInfo
     */
    public function setUserId(string $userId): UserInfo {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return UserInfo
     */
    public function setName(string $name): UserInfo {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @param string $email
     * @return UserInfo
     */
    public function setEmail(string $email): UserInfo {
        $this->email = $email;
        return $this;
    }


}