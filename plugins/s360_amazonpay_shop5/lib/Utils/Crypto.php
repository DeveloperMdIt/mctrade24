<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;

/**
 * Provides functionality for cryptography, e.g. Key generation
 */
class Crypto {

    private const KEY_LENGTH = 2048;

    /** @var Config */
    private $config;
    private static $instance;

    /**
     * Gets the config instance.
     * @return Config
     */
    public static function getInstance(): Crypto {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
       $this->config = Config::getInstance();
    }

    public function createKeyPair(): bool {
        $adapter = new ApiAdapter();
        $createdKeys = $adapter->createRSAKey(self::KEY_LENGTH);
        if(empty($createdKeys) || empty($createdKeys['publickey']) || empty($createdKeys['privatekey'])) {
            return false;
        }
        $this->config->setPrivateKey($createdKeys['privatekey']);
        $this->config->setPublicKey($createdKeys['publickey']);
        $this->config->setPublicKeyId(''); // by definition the public key id should now be considered invalid!
        $this->createKeyExchangeToken(); // (re-)generate key token
        return true;
    }

    public function createKeyExchangeToken(): string {
        $token = bin2hex(random_bytes(32));
        $this->config->setKeyExchangeToken($token);
        return $token;
    }
}