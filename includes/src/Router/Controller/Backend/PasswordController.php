<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PasswordController
 * @package JTL\Router\Controller\Backend
 */
class PasswordController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/pass');

        $step = 'prepare';
        $this->alertService->addWarning(
            \__('warningPasswordResetAuth'),
            'warningPasswordResetAuth',
            ['dismissable' => false]
        );
        if (isset($_POST['mail']) && Form::validateToken()) {
            $this->account->prepareResetPassword(Text::filterXSS(Request::pString('mail')));
        } elseif (
            isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpm'], $_POST['fpwh'])
            && Form::validateToken()
        ) {
            if (Request::pString('pw_new') === Request::pString('pw_new_confirm')) {
                $verified = $this->account->verifyResetPasswordHash(Request::pString('fpwh'), Request::pString('fpm'));
                if ($verified === true) {
                    $upd = (object)[
                        'cPass' => Shop::Container()->getPasswordService()->hash(Request::pString('pw_new'))
                    ];
                    if ($this->db->update('tadminlogin', 'cMail', Request::pString('fpm'), $upd) > 0) {
                        return $this->redirectSuccess();
                    }
                    $this->alertService->addError(\__('errorPasswordChange'), 'errorPasswordChange');
                } else {
                    $this->alertService->addError(\__('errorHashInvalid'), 'errorHashInvalid');
                }
            } else {
                $this->alertService->addError(\__('errorPasswordMismatch'), 'errorPasswordMismatch');
            }
            $smarty->assign('fpwh', Text::filterXSS(Request::pString('fpwh')))
                ->assign('fpm', Text::filterXSS(Request::pString('fpm')));
            $step = 'confirm';
        } elseif (isset($_GET['fpwh'], $_GET['mail'])) {
            $smarty->assign('fpwh', Text::filterXSS(Request::gString('fpwh')))
                ->assign('fpm', Text::filterXSS(Request::gString('mail')));
            $step = 'confirm';
        }

        return $smarty->assign('step', $step)
            ->assign('route', $this->route)
            ->getResponse('pass.tpl');
    }

    /**
     * @return ResponseInterface
     */
    private function redirectSuccess(): ResponseInterface
    {
        $this->alertService->addSuccess(
            \__('successPasswordChange'),
            'successPasswordChange',
            ['saveInSession' => true]
        );
        return new RedirectResponse($this->baseURL . '/?pw_updated=true');
    }
}
