<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

/**
 * Class SecurityIntern
 * @package Plugin\jtl_search
 */
class SecurityIntern implements ISecurity
{
    /**
     * @var string
     */
    private string $sha1Key = '';

    /**
     * @var array
     */
    private array $params = [];

    /**
     * @var string|null
     */
    public ?string $projectID = null;

    /**
     * @var string|null
     */
    public ?string $authHash = null;

    /**
     * SecurityIntern constructor.
     */
    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function createKey(bool $returnKey = true): bool|string
    {
        if (!\defined('JTLSEARCH_SECRET_KEY') || \count($this->params) === 0) {
            return false;
        }
        $this->sha1Key = \JTLSEARCH_SECRET_KEY;
        foreach ($this->params as $param) {
            $this->sha1Key .= '.' . $param;
        }

        $this->sha1Key = \sha1($this->sha1Key);

        if ($returnKey) {
            return $this->sha1Key;
        }

        return true;
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
     * @inheritdoc
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
