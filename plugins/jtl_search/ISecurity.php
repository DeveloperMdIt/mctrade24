<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

/**
 * Interface ISecurity
 * @package Plugin\jtl_search
 */
interface ISecurity
{
    /**
     * @param bool $returnKey
     * @return string|bool
     */
    public function createKey(bool $returnKey = true): bool|string;

    /**
     * @param array $params
     * @return ISecurity
     */
    public function setParams(array $params): ISecurity;

    /**
     * @return string
     */
    public function getSHA1Key(): string;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param string $projectID
     * @return ISecurity
     */
    public function setProjectId($projectID): ISecurity;

    /**
     * @param string $hash
     * @return ISecurity
     */
    public function setAuthHash($hash): ISecurity;
}
