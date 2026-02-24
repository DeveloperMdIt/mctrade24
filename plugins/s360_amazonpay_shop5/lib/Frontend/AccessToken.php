<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Frontend;

/**
 * Class AccessToken
 *
 * Represents an Amazon Pay access token with its additional information.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Frontend
 */
class AccessToken {

    private const EXPIRATION_DELTA_SECONDS = 60; // amount in seconds that a still valid token will be considered already expired in advance

    /**
     * The actual access token.
     * @var string $accessToken 
     */
    private $accessToken;

    /**
     * Refresh token that can be used to get a new access token.
     * @var string $refreshToken 
     */
    private $refreshToken;

    /**
     * The type of token returned. Should be bearer.
     * @var string $tokenType 
     */
    private $tokenType;

    /**
     * Unix timestamp of the retrieval of this token
     * @var int $creationTimestamp 
     */
    private $creationTimestamp;

    /**
     * Number of seconds before the access token becomes invalid.
     * @var int $expiresIn 
     */
    private $expiresIn;

    /**
     * Expiration timestamp, is the sum of creation and expires in.
     * @var int $expirationTimestamp 
     */
    private $expirationTimestamp;

    /**
     * @return string
     */
    public function getAccessToken(): string {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return AccessToken
     */
    public function setAccessToken(string $accessToken): AccessToken {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return AccessToken
     */
    public function setRefreshToken(string $refreshToken): AccessToken {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenType(): string {
        return $this->tokenType;
    }

    /**
     * @param string $tokenType
     * @return AccessToken
     */
    public function setTokenType(string $tokenType): AccessToken {
        $this->tokenType = $tokenType;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreationTimestamp(): int {
        return $this->creationTimestamp;
    }

    /**
     * @param int $creationTimestamp
     * @return AccessToken
     */
    public function setCreationTimestamp(int $creationTimestamp): AccessToken {
        $this->creationTimestamp = $creationTimestamp;
        $this->recomputeExpirationTimestamp();
        return $this;
    }

    /**
     * @return int
     */
    public function getExpiresIn(): int {
        return $this->expiresIn;
    }

    /**
     * @param int $expiresIn
     * @return AccessToken
     */
    public function setExpiresIn(int $expiresIn): AccessToken {
        $this->expiresIn = $expiresIn;
        $this->recomputeExpirationTimestamp();
        return $this;
    }

    /**
     * Checks if this token is expired (or close to expiring)
     * @return bool true iff the access token is expired or very close to expiring.
     */
    public function isExpired(): bool {
        return time() > $this->expirationTimestamp;
    }

    /**
     * Re-computes the expiration timestamp.
     */
    private function recomputeExpirationTimestamp(): void {
        if(null !== $this->creationTimestamp && null !== $this->expiresIn) {
            $this->expirationTimestamp = $this->creationTimestamp + $this->expiresIn + self::EXPIRATION_DELTA_SECONDS;
        }
    }

    /**
     * @return int
     */
    public function getExpirationTimestamp(): int {
        return $this->expirationTimestamp;
    }

}