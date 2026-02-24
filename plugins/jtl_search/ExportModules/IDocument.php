<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Interface IDocument
 * @package Plugin\jtl_search\ExportModules
 */
interface IDocument
{
    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return string
     */
    public function getClassName(): string;

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string|null $languageISO
     * @return string|array|null
     */
    public function getURL(?string $languageISO = null);

    /**
     * @param int $nId
     * @return mixed
     */
    public function setId($nId);

    /**
     * @param string $name
     * @param string $languageISO
     * @return mixed
     */
    public function setName($name, $languageISO);

    /**
     * @param string $url
     * @param string $languageISO
     * @return mixed
     */
    public function setURL($url, $languageISO);
}
