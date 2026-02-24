<?php

declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConsentController
 * @package JTL\Router\Controller
 */
class ConsentController extends AbstractController
{
    public function register(RouteGroup $route, string $dynName): void
    {
        $route->post('/_updateconsent', $this->getResponse(...))
            ->setName('ROUTE_UPDATE_CONSENTPOST' . $dynName);
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        if (!Form::validateToken()) {
            return new JsonResponse(['status' => 'FAILED', 'data' => 'Invalid token']);
        }

        return new JsonResponse([
            'status' => 'OK',
            'data'   => Shop::Container()->getConsentManager()->save($request->getParsedBody()['data'] ?? '')
        ]);
    }
}
