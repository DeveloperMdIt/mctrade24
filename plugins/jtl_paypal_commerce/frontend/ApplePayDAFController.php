<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use JTL\Router\Controller\PageController;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DAFController
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class ApplePayDAFController extends PageController
{
    public const DAF_ROUTE          = '/.well-known/apple-developer-merchantid-domain-association';
    public const DAF_DOWNLOAD_ROUTE = '/.well-known/download/apple-developer-merchantid-domain-association';
    public const DAF_VERSION        = '1.0';

    /** @var string[] */
    private static array $dafContent = [];

    /**
     * @param string $workingMode
     * @param string $version
     * @return string
     */
    private static function getDAFContent(string $workingMode, string $version = self::DAF_VERSION): string
    {
        $workingMode .= '-' . $version;
        if (isset(self::$dafContent[$workingMode])) {
            return self::$dafContent[$workingMode];
        }

        $content = \file_get_contents(__DIR__ . '/daf/apple-developer-merchantid-domain-association-' . $workingMode);
        if ($content !== false) {
            return (self::$dafContent[$workingMode] = $content);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $content = self::getDAFContent($this->config['workingMode'], $this->config['version']);
        if ($content !== '') {
            $response = (new Response())->withStatus(200)
                                        ->withAddedHeader('Content-Type', 'text/html');
            $response->getBody()->write($content);
        } else {
            $logger = new Logger(Logger::TYPE_INFORMATION);
            $logger->write(\LOGLEVEL_NOTICE, 'ApplePayDAFController return 404: workingMode '
                . $this->config['workingMode'] . ', '
                . ($this->config['activated'] !== 'Y' ? 'not ' : '') . 'activated');

            $response = (new Response())->withStatus(404);
        }

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @param JTLSmarty              $smarty
     * @return ResponseInterface
     */
    public function getDownloadResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $content = self::getDAFContent($this->config['workingMode'], $this->config['version']);
        if ($content !== '') {
            $response = (new Response())->withStatus(200)
                                        ->withAddedHeader('Content-Type', 'application/octet-stream')
                                        ->withAddedHeader('Content-Disposition', 'attachment; '
                                            . 'filename="apple-developer-merchantid-domain-association"');
            $response->getBody()->write($content);
        } else {
            $logger = new Logger(Logger::TYPE_INFORMATION);
            $logger->write(\LOGLEVEL_NOTICE, 'ApplePayDAFController return 404: workingMode '
                . $this->config['workingMode'] . ', '
                . ($this->config['activated'] !== 'Y' ? 'not ' : '') . 'activated');

            $response = (new Response())->withStatus(404);
        }

        return $response;
    }

    /**
     * @return string
     */
    public static function getDAFRoute(): string
    {
        return 'https://' . \parse_url(Shop::getURL(), \PHP_URL_HOST) . self::DAF_ROUTE;
    }

    /**
     * @return string
     */
    public static function getDAFDownloadRoute(): string
    {
        return Shop::getURL() . self::DAF_DOWNLOAD_ROUTE;
    }

    /**
     * @param string $workingMode
     * @param string $version
     * @return bool
     */
    public static function testRoute(string $workingMode, string $version): bool
    {
        $client = new Client(['timeout' => 5]);
        try {
            \session_write_close();
            $response = $client->request(MethodType::GET, new Uri(self::getDAFRoute()));
            \session_start();
        } catch (GuzzleException $e) {
            \session_start();
            $logger = new Logger(Logger::TYPE_INFORMATION);
            $logger->write(\LOGLEVEL_ERROR, 'ApplePayDAFController::testRoute failed: ' . $e->getMessage());

            return false;
        }

        return \trim($response->getBody()->getContents()) === self::getDAFContent($workingMode, $version);
    }
}
