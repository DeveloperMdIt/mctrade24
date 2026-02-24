<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use JsonException;

use function Functional\first;

/**
 * Class ClientErrorResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class ClientErrorResponse extends JSONResponse
{
    /** @var ClientError|null */
    private ?ClientError $clientError = null;

    /**
     * @return ClientError
     * @throws JsonException | UnexpectedResponseException
     */
    protected function getClientError(): ClientError
    {
        if ($this->clientError === null) {
            $data = $this->getData();

            $this->clientError = ($data->errors ?? null) !== null
                ? new ClientError(first($data->errors))
                : new ClientError($data);
        }

        return $this->clientError;
    }

    /**
     * @inheritDoc
     */
    public function getExpectedResponseCode(): array
    {
        // current status code is the expected
        return [$this->getStatusCode()];
    }

    /**
     * @return string
     */
    public function getDebugId(): string
    {
        try {
            return $this->getClientError()->getDebugId();
        } catch (JsonException | UnexpectedResponseException) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        try {
            return $this->getClientError()->getName();
        } catch (JsonException | UnexpectedResponseException) {
            return 'Unknown client error';
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        try {
            return $this->getClientError()->getMessage();
        } catch (JsonException | UnexpectedResponseException) {
            $reason = $this->getReasonPhrase();

            return $reason === '' ? 'Unspecified error message' : $reason;
        }
    }

    /**
     * @param int $pos
     * @return object|null
     */
    public function getDetail(int $pos = 0): ?ClientErrorDetail
    {
        try {
            return $this->getClientError()->getDetail($pos);
        } catch (JsonException | UnexpectedResponseException) {
            return null;
        }
    }

    /**
     * @param string $rel
     * @return string|null
     */
    public function getLink(string $rel): ?string
    {
        try {
            return $this->getClientError()->getLink($rel);
        } catch (JsonException | UnexpectedResponseException) {
            return null;
        }
    }
}
