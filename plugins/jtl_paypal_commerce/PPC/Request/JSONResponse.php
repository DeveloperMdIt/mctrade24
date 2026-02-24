<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use JsonException;

/**
 * Class JSONResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class JSONResponse extends PPCResponse
{
    /** @var string|null */
    private ?string $content = null;

    /** @var bool */
    private bool $contentLoaded = false;

    /** @var int[] */
    protected array $responseCodes = [200];

    /**
     * @return string|null
     * @throws UnexpectedResponseException
     */
    protected function getContent(): ?string
    {
        if (!\in_array($this->getStatusCode(), $this->getExpectedResponseCode(), true)) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode());
        }
        if (!$this->contentLoaded) {
            $body = $this->getBody();

            $this->content       = $body->getContents();
            $this->contentLoaded = true;
        }

        return \is_string($this->content) ? $this->content : null;
    }

    /**
     * @return string|array|object|null
     * @throws JsonException
     * @throws UnexpectedResponseException
     */
    public function getData(): string|array|object|null
    {
        $content = $this->getContent();

        return $content !== null ? \json_decode($content, false, 512, \JSON_THROW_ON_ERROR) : null;
    }

    /**
     * @return int[]
     */
    public function getExpectedResponseCode(): array
    {
        return $this->responseCodes;
    }

    /**
     * @param int[] $responseCodes
     * @return static
     */
    public function setExpectedResponseCode(array $responseCodes): static
    {
        $this->responseCodes = $responseCodes;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->getContent() ?? '';
        } catch (UnexpectedResponseException $e) {
            return $e->getMessage();
        }
    }
}
