<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Greew\OAuth2\Client\Provider\Azure;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use League\OAuth2\Client\Provider\Google;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OAuthAuthorizationController extends AbstractBackendController
{
    protected function redirectToAuthUrl(): ResponseInterface
    {
        $redirectUri  = $this->baseURL . $this->route;
        $method       = Request::gString('method');
        $clientId     = Request::gString('client_id');
        $clientSecret = Request::gString('client_secret');
        $tenantId     = Request::gString('tenant_id');
        $oauthOptions = [
            'method'       => $method,
            'clientId'     => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri'  => $redirectUri,
            'accessType'   => 'offline',
        ];

        if ($method === 'outlook') {
            $oauthOptions['tenantId'] = $tenantId;
            $provider                 = new Azure($oauthOptions);
            $authUrl                  = $provider->getAuthorizationUrl([
                'scope' => ['https://outlook.office.com/SMTP.Send', 'offline_access']
            ]);
        } elseif ($method === 'gmail') {
            $provider = new Google($oauthOptions);
            $authUrl  = $provider->getAuthorizationUrl([
                'scope' => ['https://mail.google.com/']
            ]);
        } else {
            throw new \RuntimeException('Unsupported mail method: ' . $method);
        }

        $oauthOptions['oauth2state'] = $provider->getState();
        $_SESSION['oauthOptions']    = $oauthOptions;

        return new RedirectResponse($authUrl);
    }

    protected function outputReceivedTokens(JTLSmarty $smarty): ResponseInterface
    {
        $code         = Request::gString('code');
        $state        = Request::gString('state');
        $oauthOptions = $_SESSION['oauthOptions'];

        if ($state !== $oauthOptions['oauth2state']) {
            throw new \RuntimeException('Invalid state');
        }

        if ($oauthOptions['method'] === 'outlook') {
            $provider = new Azure($oauthOptions);
        } elseif ($oauthOptions['method'] === 'gmail') {
            $provider = new Google($oauthOptions);
        } else {
            throw new \RuntimeException('Unsupported mail method: ' . $oauthOptions['method']);
        }

        $token        = $provider->getAccessToken('authorization_code', ['code' => $code]);
        $accessToken  = $token->getToken();
        $refreshToken = $token->getRefreshToken();

        return $smarty
            ->assign('accessToken', $accessToken)
            ->assign('refreshToken', $refreshToken)
            ->getResponse('oauth_authorization.tpl');
    }

    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $method       = Request::gString('method');
        $clientId     = Request::gString('client_id');
        $clientSecret = Request::gString('client_secret');
        $tenantId     = Request::gString('tenant_id');
        $code         = Request::gString('code');

        if (!empty($clientId) && !empty($clientSecret) && ($method !== 'outlook' || !empty($tenantId))) {
            return $this->redirectToAuthUrl();
        } elseif (!empty($code)) {
            return $this->outputReceivedTokens($smarty);
        }

        throw new \RuntimeException('No state given');
    }
}
