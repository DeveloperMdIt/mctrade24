<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Request\ResponseErrorHandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class PPCClient
 * @package Plugin\jtl_paypal_commerce\PPC\HttpClient
 */
class PPCClient extends Client
{
    /** @var EnvironmentInterface */
    protected EnvironmentInterface $environment;

    /** @var Logger|null */
    protected ?Logger $logger;

    /**
     * PPCClient constructor.
     * @param EnvironmentInterface $environment
     * @param Logger|null          $logger
     */
    public function __construct(EnvironmentInterface $environment, ?Logger $logger = null)
    {
        $this->environment = $environment;
        $this->logger      = $logger;

        parent::__construct($this->getDefaultSettings());
    }

    /**
     * @return array
     */
    protected function getDefaultSettings(): array
    {
        return [
            'base_uri'        => $this->environment->baseUrl(),
            'http_errors'     => true,
            'decode_content'  => true,
            'verify'          => true,
            'cookies'         => false,
            'idn_conversion'  => false,
            'connect_timeout' => \PHP_SAPI === 'cli' ? 1 : 5,
        ];
    }

    /**
     * @inheritDoc
     * @throws PPCRequestException
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $metaDataId = $this->environment->getMetaDataId() ?? '';
        $requestId  = $options['PayPal-Request-Id'] ?? '';
        if ($metaDataId !== '') {
            $request = $request->withAddedHeader('PayPal-Client-Metadata-Id', $metaDataId);
        }
        if ($requestId !== '') {
            $request = $request->withAddedHeader('PayPal-Request-Id', $requestId);
        }

        try {
            if ($this->logger !== null) {
                $cpy = clone $request;
                $this->logger->write(\LOGLEVEL_DEBUG, 'PPCClient send request: ', [
                    'path'   => $cpy->getUri()->getPath(),
                    'header' => $cpy->getHeaders(),
                    'body'   => $cpy->getBody()->getContents(),
                ]);
            }

            return parent::send($request, $options);
        } catch (GuzzleException $e) {
            if ($request instanceof ResponseErrorHandlerInterface) {
                if ($e instanceof ClientException) {
                    return $request->handleClientError($e);
                }
                if ($e instanceof ServerException) {
                    return $request->handleServerError($e);
                }
            }

            throw $e;
        }
    }
}
