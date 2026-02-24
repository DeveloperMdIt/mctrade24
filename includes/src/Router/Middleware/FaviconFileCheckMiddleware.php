<?php

declare(strict_types=1);

namespace JTL\Router\Middleware;

use Exception;
use Intervention\Image\ImageManager;
use JTL\Media\Image;
use JTL\Shop;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class PhpFileCheckMiddleware
 * @package JTL\Router\Middleware
 */
class FaviconFileCheckMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var string|null $file */
        $file = $request->getAttribute('file');
        if ($file === null) {
            return $handler->handle($request);
        }
        $smarty = Shop::Smarty();
        /** @var string $templateDir */
        $templateDir = $smarty->getTemplateDir($smarty->context);
        $faviconPath = $templateDir . 'favicon/';

        // generate all supported favicons from image upload in template settings
        switch ($file) {
            case 'android-chrome-512x512.png':
                $this->generatePNG($file, 512, 512, $faviconPath);
                break;
            case 'android-chrome-192x192.png':
                $this->generatePNG($file, 192, 192, $faviconPath);
                break;
            case 'apple-touch-icon.png':
                $this->generatePNG($file, 180, 180, $faviconPath);
                break;
            case 'mstile-70x70.png':
                $this->generatePNG($file, 70, 70, $faviconPath);
                break;
            case 'mstile-144x144.png':
                $this->generatePNG($file, 144, 144, $faviconPath);
                break;
            case 'mstile-150x150.png':
                $this->generatePNG($file, 150, 150, $faviconPath);
                break;
            case 'mstile-310x150.png':
                $this->generatePNG($file, 310, 150, $faviconPath);
                break;
            case 'mstile-310x310.png':
                $this->generatePNG($file, 310, 310, $faviconPath);
                break;
            case 'site.webmanifest':
                $this->generateWebmanifest($file, $faviconPath);
                break;
            case 'browserconfig.xml':
                $this->generateBrowserconfig($file, $faviconPath);
                break;
            default:
                break;
        }

        return $handler->handle($request);
    }

    /**
     * @throws Exception
     */
    private function generatePNG(string $fileName, int $width, int $height, string $targetPath): void
    {
        if (\is_file($targetPath . $fileName)) {
            return;
        }

        $sourcePath = $targetPath . 'favicon.ico';
        if (!\is_file($sourcePath)) {
            $sourcePath = $targetPath . 'favicon-default.png';
            if (!\is_file($sourcePath)) {
                throw new Exception(\sprintf('Image "%s" does not exist', $sourcePath));
            }
        }

        $thumbnail = $targetPath . $fileName;
        $manager   = new ImageManager(Image::getImageDriver());
        $img       = $manager->read($sourcePath);
        $ext       = 'png';
        $canvas    = $manager->create($width, $height)->fill('rgba(255, 255, 255, 0)');
        if ($img->width() > $width || $img->height() > $height) {
            $img->scaleDown($width, $height);
        }
        $canvas->place($img, 'center');
        $img = $canvas;

        $img->save($thumbnail, 100, $ext);
    }

    private function generateWebmanifest(string $fileName, string $targetPath): void
    {
        if (\is_file($targetPath . $fileName)) {
            return;
        }

        $jsonData = '{
            "name": "",
            "short_name": "",
            "icons": [
                {
                    "src": "/android-chrome-192x192.png",
                    "sizes": "192x192",
                    "type": "image/png"
                },
                {
                    "src": "/android-chrome-512x512.png",
                    "sizes": "512x512",
                    "type": "image/png"
                }
            ],
            "theme_color": "#ffffff",
            "background_color": "#ffffff",
            "display": "standalone"
        }';

        $arrayData               = \json_decode($jsonData, true, 512, \JSON_THROW_ON_ERROR);
        $conf                    = Shop::getSettings([\CONF_GLOBAL]);
        $arrayData['name']       = $conf['global']['global_shopname'];
        $arrayData['short_name'] = '';
        // theme color hinzufügen?

        $modifiedJsonData = \json_encode($arrayData, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);

        \file_put_contents($targetPath . $fileName, $modifiedJsonData);
    }

    private function generateBrowserconfig(string $fileName, string $targetPath): void
    {
        if (\is_file($targetPath . $fileName)) {
            return;
        }

        $xmlData = '<?xml version="1.0" encoding="utf-8"?>
            <browserconfig>
                <msapplication>
                    <tile>
                        <square70x70logo src="/mstile-70x70.png"/>
                        <square150x150logo src="/mstile-150x150.png"/>
                        <square310x310logo src="/mstile-310x310.png"/>
                        <wide310x150logo src="/mstile-310x150.png"/>
                        <TileColor>#f8bf00</TileColor>
                    </tile>
                </msapplication>
            </browserconfig>';

        \file_put_contents($targetPath . $fileName, $xmlData);
    }
}
