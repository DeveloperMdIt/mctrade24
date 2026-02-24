<?php

namespace Plugin\jtl_search\ExportModules;

use stdClass;

/**
 * Class Document
 * @package Plugin\jtl_search\ExportModules
 */
abstract class Document
{
    /**
     * @var string
     */
    protected $cLanguageIso;

    /**
     * @var int
     */
    protected $kProduct;

    /**
     * @return string
     */
    public function getLanguageIso()
    {
        return $this->cLanguageIso;
    }

    /**
     * @param string $languageISO
     * @return $this
     */
    public function setLanguageIso($languageISO): self
    {
        $this->cLanguageIso = $languageISO;

        return $this;
    }

    /**
     * @param int $kProduct
     * @return $this
     */
    public function setProduct(int $kProduct): self
    {
        $this->kProduct = $kProduct;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Document constructor.
     * @param null $obj
     */
    public function __construct($obj = null)
    {
        if (isset($obj) && \is_object($obj)) {
            $this->toObject($obj);
        }
    }

    /**
     * @param bool $asObject
     * @return false|stdClass|string
     */
    private function toJSON(bool $asObject = false)
    {
        $res = new stdClass();
        foreach (\get_object_vars($this) as $varName => $varValue) {
            if (isset($varValue)) {
                if (\is_array($varValue)) {
                    foreach ($varValue as $key => $value) {
                        if (\is_object($value)) {
                            $res->{$varName}[$key] = $value->toJSON(true);
                        } else {
                            $res->{$varName}[$key] = $this->convertUTF8($value);
                        }
                    }
                } else {
                    $res->{$varName} = $this->convertUTF8($varValue);
                }
            }
        }
        if ($asObject) {
            return $res;
        }
        $res->cObjectType = \str_replace('Plugin\\jtl_search\\ExportModules\\\\', '', $this->getClassName());
        $res->cObjectType = \str_replace('Plugin\jtl_search\ExportModules\\', '', $res->cObjectType);

        return \json_encode($res);
    }

    /**
     * @param mixed $object
     * @return object|bool
     */
    private function toObject($object)
    {
        if ($this->classMatches($object->cObjectType)) {
            unset($object->cObjectType);
            foreach (\get_object_vars($object) as $key => $value) {
                if (isset($value)) {
                    $this->{$key} = $value;
                }
            }
        } else {
            return false;
        }

        return $object;
    }

    /**
     * @param string $className
     * @return bool
     */
    private function classMatches(string $className): bool
    {
        $class = $this->getClassName();

        return $className === $class || $className === ('Plugin\jtl_search\ExportModules\\' . $class);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->convertUTF8($this->toJSON() . "\n");
    }

    /**
     * @param string $string
     * @return string
     */
    protected function prepareString($string): string
    {
        return \trim(\strip_tags(\str_replace('>', '> ', $string)));
    }

    /**
     * @param string $data
     * @return string
     */
    protected function convertUTF8($data)
    {
        return \mb_convert_encoding($data, 'UTF-8', \mb_detect_encoding($data, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
    }
}
