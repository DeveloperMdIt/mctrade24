<?php

declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OrderStatusController
 * @package JTL\Router\Controller
 */
class OrderStatusController extends PageController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_BESTELLSTATUS);
        $uid = Request::verifyGPDataString('uid');
        if (empty($uid)) {
            return $this->handleInvalidUid();
        }
        $customer = Frontend::getCustomer();
        $status   = $this->db->getSingleObject(
            'SELECT kBestellung, failedAttempts
                FROM tbestellstatus 
                WHERE dDatum >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    AND cUID = :uid
                    AND (failedAttempts <= :maxAttempts OR 1 = :loggedIn)',
            [
                'uid'         => $uid,
                'maxAttempts' => (int)$this->config['kunden']['kundenlogin_max_loginversuche'],
                'loggedIn'    => $customer->isLoggedIn() ? 1 : 0,
            ]
        );
        if ($status === null) {
            return $this->handleInvalidStatus();
        }
        $order    = new Bestellung((int)$status->kBestellung, true, $this->db);
        $plzValid = $this->isPlzValid($order->oRechnungsadresse?->cPLZ, $uid, (int)$status->failedAttempts);

        $this->smarty->assign('Bestellung', $order)
            ->assign('uid', Text::filterXSS($uid))
            ->assign('showLoginPanel', $customer->getID() === $order->kKunde);

        if ($plzValid || $customer->getID() === $order->kKunde) {
            $this->db->update(
                'tbestellstatus',
                'cUID',
                $uid,
                (object)['failedAttempts' => 0]
            );
            $this->smarty->assign(
                'Kunde',
                $customer->getID() === $order->kKunde
                    ? $customer
                    : new Customer($order->kKunde, null, $this->db)
            )
                ->assign('Lieferadresse', $order->Lieferadresse)
                ->assign('billingAddress', $order->oRechnungsadresse)
                ->assign('incommingPayments', $order->getIncommingPayments(true, true));
        }

        $this->smarty->assign('step', 'bestellung')
            ->assign('Link', Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_LOGIN));

        $this->preRender();

        return $this->smarty->getResponse('account/index.tpl');
    }

    private function handleInvalidUid(): ResponseInterface
    {
        $this->alertService->addDanger(
            Shop::Lang()->get('uidNotFound', 'errorMessages'),
            'wrongUID',
            ['saveInSession' => true]
        );

        return new RedirectResponse(Shop::Container()->getLinkService()->getStaticRoute('jtl.php'), 303);
    }

    private function handleInvalidStatus(): ResponseInterface
    {
        $this->alertService->addDanger(
            Shop::Lang()->get('statusOrderNotFound', 'errorMessages'),
            'statusOrderNotFound',
            ['saveInSession' => true]
        );

        return new RedirectResponse(Shop::Container()->getLinkService()->getStaticRoute('jtl.php'), 303);
    }

    public function isPlzValid(?string $orderPLZ, string $uid, int $failedAttempts): bool
    {
        if (!Form::validateToken()) {
            return false;
        }
        if (isset($_POST['plz']) && $orderPLZ === Text::filterXSS($_POST['plz'])) {
            return true;
        }
        if (!empty($_POST['plz'])) {
            $this->db->update(
                'tbestellstatus',
                'cUID',
                $uid,
                (object)['failedAttempts' => $failedAttempts + 1]
            );
            $this->alertService->addDanger(Shop::Lang()->get('incorrectLogin'), 'statusOrderincorrectLogin');
        }

        return false;
    }
}
