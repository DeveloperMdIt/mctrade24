<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

/**
 * Class Security
 * @package Plugin\jtl_search
 */
class Security implements ISecurity
{
    /**
     * @var string
     */
    private $sha1Key;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $projectID;

    /**
     * @var string
     */
    private $authHash;

    /**
     * @param string $projectID
     * @param string $authHash
     */
    public function __construct($projectID, $authHash)
    {
        $this->projectID = $projectID;
        $this->authHash  = $authHash;
    }

    /**
     * @inheritdoc
     */
    public function createKey(bool $returnKey = true): bool|string
    {
        if (
            \is_array($this->params)
            && \count($this->params) > 0
            && \strlen($this->authHash) > 0
            && \strlen($this->projectID) > 0
        ) {
            $this->sha1Key = $this->authHash . '.' . $this->projectID;
            foreach ($this->params as $param) {
                $this->sha1Key .= '.' . $param;
            }
            $this->sha1Key = \sha1($this->sha1Key);

            return $returnKey ? $this->sha1Key : true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function setParams(array $params): ISecurity
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSHA1Key(): string
    {
        return $this->sha1Key;
    }

    /**
     * @inheritdoc
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param string $projectID
     * @return $this
     */
    public function setProjectId($projectID): ISecurity
    {
        $this->projectID = $projectID;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAuthHash($hash): ISecurity
    {
        $this->authHash = $hash;

        return $this;
    }
}
