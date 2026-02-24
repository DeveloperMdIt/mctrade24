<?php

declare(strict_types=1);

namespace JTL\Router\Controller;

use Illuminate\Support\Collection;
use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Helpers\Form;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class WishlistController
 * @package JTL\Router\Controller
 */
class WishlistController extends AbstractController
{
    private int $wishlistID = 0;

    private Wishlist $wishlist;

    private ?string $step = null;

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
        Shop::setPageType(\PAGE_WUNSCHLISTE);
        $this->wishlistID = $this->initWishlistID();
        $urlID            = Text::filterXSS(Request::verifyGPDataString('wlid'));
        $searchQuery      = Text::filterXSS(Request::verifyGPDataString('cSuche'));
        $customerID       = Frontend::getCustomer()->getID();
        $this->initWishlist($customerID, $searchQuery);
        $action = $this->getAction();
        $this->handleAction($action, $searchQuery);
        $response = $this->handleErrors($customerID, $urlID, $action);
        if ($response instanceof ResponseInterface) {
            return $response;
        }
        $this->setPagination();
        $this->smarty->assign('CWunschliste', $this->wishlist)
            ->assign('newWL', Request::verifyGPCDataInt('newWL'))
            ->assign('wlsearch', $searchQuery)
            ->assign('Link', $this->currentLink)
            ->assign(
                'isCurrenctCustomer',
                $this->wishlist->getCustomerID() > 0 && $this->wishlist->getCustomerID() === $customerID
            )
            ->assign('cURLID', $urlID)
            ->assign('step', $this->step);

        $this->preRender();
        $this->addCampaignAction();

        return $this->smarty->getResponse('snippets/wishlist.tpl');
    }

    private function handleAction(string $action, string $searchQuery): void
    {
        if ($action === '' || !Form::validateToken()) {
            return;
        }
        if (Request::pString('kWunschliste') > 0) {
            $this->handlePostAction($searchQuery, $action);
        } elseif ($action === 'search' && $this->wishlistID > 0) {
            $this->wishlist = Wishlist::instanceByID($this->wishlistID, $this->db)->filterPositions($searchQuery);
        }
    }

    private function handleErrors(int $customerID, string $urlID, string $action): ResponseInterface|false
    {
        $wishlists  = [];
        $linkHelper = Shop::Container()->getLinkService();
        if (Request::verifyGPCDataInt('wlidmsg') > 0) {
            $this->alertService->addNotice(Wishlist::mapMessage(Request::verifyGPCDataInt('wlidmsg')), 'wlidmsg');
        }
        if (Request::verifyGPCDataInt('error') === 1) {
            $this->addNotFoundError($urlID);
        } elseif (!$this->wishlistID) {
            if ($customerID > 0) {
                $this->wishlist   = Wishlist::buildPrice(Wishlist::instanceByCustomerID($customerID));
                $this->wishlistID = $this->wishlist->getID();
            }
            if (!$this->wishlistID) {
                return new RedirectResponse($linkHelper->getStaticRoute('jtl.php') . '?r=' . \R_LOGIN_WUNSCHLISTE);
            }
        }
        $this->currentLink = ($this->state->linkID > 0) ? $linkHelper->getPageLink($this->state->linkID) : null;
        if ($customerID > 0) {
            $wishlists = $this->getWishlists($action);
        } elseif ($this->wishlist->getID() === 0) {
            return new RedirectResponse($linkHelper->getStaticRoute('jtl.php') . '?r=' . \R_LOGIN_WUNSCHLISTE);
        }
        $this->smarty->assign('oWunschliste_arr', $wishlists);

        return false;
    }

    private function initWishlistID(): int
    {
        return (Request::verifyGPCDataInt('wl') > 0 && Request::verifyGPCDataInt('wlvm') === 0)
            ? Request::verifyGPCDataInt('wl') // one of multiple customer wishlists
            : $this->state->wishlistID;
    }

    private function initWishlist(int $customerID, string $searchQuery): void
    {
        if ($this->wishlistID === 0 && $customerID > 0) {
            if (Frontend::getWishList()->getID() <= 0) {
                $this->wishlist = new Wishlist(0, $this->db);
                $this->wishlist->schreibeDB();
                $_SESSION['Wunschliste'] = $this->wishlist;
            } else {
                $this->wishlist = Wishlist::buildPrice(Wishlist::instanceByCustomerID($customerID));
            }
        } else {
            $this->wishlist = Wishlist::buildPrice(
                Wishlist::instanceByID($this->wishlistID, $this->db)->filterPositions($searchQuery)
            );
        }
        $this->wishlistID = $this->wishlist->getID();
    }

    private function setPagination(): void
    {
        $wishListItems = $this->wishlist->getItems();

        $pagination = (new Pagination())
            ->setItemArray($wishListItems)
            ->setItemCount(\count($wishListItems))
            ->assemble();

        $this->smarty->assign('pagination', $pagination)
            ->assign('wishlistItems', $pagination->getPageItems())
            ->assign('hasItems', \count($wishListItems) > 0);
    }

    private function addCampaignAction(): void
    {
        if ($this->wishlist->getID() <= 0) {
            return;
        }
        $campaign = new Campaign(\KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL, $this->db);
        if (
            isset($campaign->kKampagne, $campaign->cWert)
            && \mb_convert_case($campaign->cWert, \MB_CASE_LOWER) ===
            \strtolower(Request::verifyGPDataString($campaign->cParameter))
        ) {
            $campaign->trackHit(Frontend::getVisitor()->kBesucher ?? 0);
        }
    }

    private function addNotFoundError(string $urlID): void
    {
        $wl = Wishlist::instanceByURLID($urlID);
        if ($wl->isPublic()) {
            $this->alertService->addError(
                \sprintf(Shop::Lang()->get('nowlidWishlist', 'messages'), $urlID),
                'nowlidWishlist',
                ['saveInSession' => true]
            );
        }
    }

    /**
     * @return Collection<Wishlist>
     */
    private function getWishlists(string $action): Collection
    {
        $wishlists          = Wishlist::getWishlists();
        $invisibleItemCount = Wishlist::getInvisibleItemCount(
            $wishlists,
            $this->wishlist,
            $this->wishlistID
        );
        if ($invisibleItemCount <= 0) {
            return $wishlists;
        }
        if ($action === 'search') {
            $this->alertService->addInfo(
                \sprintf(Shop::Lang()->get('infoItemsFound', 'wishlist'), \count($this->wishlist->getItems())),
                'infoItemsFound'
            );
        } else {
            $this->alertService->addWarning(
                \sprintf(Shop::Lang()->get('warningInvisibleItems', 'wishlist'), $invisibleItemCount),
                'warningInvisibleItems'
            );
        }

        return $wishlists;
    }

    private function addToCart(int $wishlistItemID, Wishlist $filteredWishlist): void
    {
        $position = Wishlist::getWishListPositionDataByID($wishlistItemID);
        if (
            !isset($position->kArtikel)
            || $position->kArtikel <= 0
            || (int)$position->kWunschliste !== $filteredWishlist->getID()
        ) {
            return;
        }
        $attributeValues = Product::isVariChild($position->kArtikel)
            ? Product::getVarCombiAttributeValues($position->kArtikel)
            : Wishlist::getAttributesByID($this->wishlistID, $position->kWunschlistePos);
        if (!$position->bKonfig) {
            CartHelper::addProductIDToCart(
                $position->kArtikel,
                $position->fAnzahl,
                $attributeValues ?: []
            );
        }
        $this->alertService->addNotice(Shop::Lang()->get('basketAdded', 'messages'), 'basketAdded');
    }

    private function sendViaMail(Wishlist $filteredWishlist): void
    {
        if (
            $filteredWishlist->getURL() === ''
            || !$filteredWishlist->isPublic()
            || !$filteredWishlist->isSelfControlled()
        ) {
            return;
        }
        $this->step = 'wunschliste anzeigen';
        if (Request::pInt('send') === 1) {
            if ($this->config['global']['global_wunschliste_anzeigen'] === 'Y') {
                $mails = \explode(' ', Text::filterXSS(Request::pString('email')));
                $this->alertService->addNotice(Wishlist::send($mails, $this->wishlistID), 'sendWL');
                $this->wishlist = Wishlist::buildPrice(
                    Wishlist::instanceByID($this->wishlistID, $this->db)
                );
            }
        } else {
            $this->step = 'wunschliste versenden';
            // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
            $this->wishlist = Wishlist::buildPrice(
                Wishlist::instanceByID($this->wishlistID, $this->db)
            );
        }
    }

    private function addAllToCart(Wishlist $filteredWishlist): void
    {
        if (\count($filteredWishlist->getItems()) === 0) {
            return;
        }
        foreach ($filteredWishlist->getItems() as $position) {
            $product         = $position->getProduct();
            $attributeValues = Product::isVariChild($position->getProductID())
                ? Product::getVarCombiAttributeValues($position->getProductID())
                : Wishlist::getAttributesByID($this->wishlistID, $position->getID());
            if (
                $product !== null
                && !$product->bHasKonfig
                && isset($product->inWarenkorbLegbar)
                && $product->inWarenkorbLegbar > 0
            ) {
                CartHelper::addProductIDToCart(
                    $position->getProductID(),
                    $position->getQty(),
                    $attributeValues ?: []
                );
            }
        }
        $this->alertService->addNotice(
            Shop::Lang()->get('basketAllAdded', 'messages'),
            'basketAllAdded'
        );
    }

    private function removeItem(int $wishlistItemID): void
    {
        if ($wishlistItemID > 0 && $this->wishlist->isSelfControlled()) {
            $this->wishlist->entfernePos($wishlistItemID);
            $this->alertService->addNotice(
                Shop::Lang()->get('wishlistUpdate', 'messages'),
                'wishlistUpdate'
            );
        }
    }

    private function removeAll(): void
    {
        if (!$this->wishlist->isSelfControlled()) {
            return;
        }
        $this->wishlist->entferneAllePos();
        if (Frontend::getWishList()->getID() === $this->wishlist->getID()) {
            Frontend::getWishList()->setItems([]);
        }
        $this->alertService->addNotice(
            Shop::Lang()->get('wishlistDelAll', 'messages'),
            'wishlistDelAll'
        );
    }

    private function update(Wishlist $filteredWishlist): void
    {
        if (!$filteredWishlist->isSelfControlled()) {
            return;
        }
        $this->alertService->addNotice(Wishlist::update($this->wishlistID), 'updateWL');
        $this->wishlist = Wishlist::buildPrice(
            Wishlist::instanceByID($this->wishlistID, $this->db)
        );

        $_SESSION['Wunschliste'] = $this->wishlist;
    }

    private function setPublic(int $wishlistTargetID): void
    {
        $list = Wishlist::instanceByID($wishlistTargetID, $this->db);
        if ($wishlistTargetID !== 0 && $list->isSelfControlled()) {
            $list->setVisibility(true);
            $this->alertService->addNotice(
                Shop::Lang()->get('wishlistSetPublic', 'messages'),
                'wishlistSetPublic'
            );
        }
    }

    private function setPrivate(int $wishlistTargetID): void
    {
        $list = Wishlist::instanceByID($wishlistTargetID, $this->db);
        if ($wishlistTargetID !== 0 && $list->isSelfControlled()) {
            $list->setVisibility(false);
            $this->alertService->addNotice(
                Shop::Lang()->get('wishlistSetPrivate', 'messages'),
                'wishlistSetPrivate'
            );
        }
    }

    private function createNew(): void
    {
        $this->alertService->addNotice(
            Wishlist::save(Text::htmlentities(Text::filterXSS(Request::pString('cWunschlisteName')))),
            'saveWL'
        );
    }

    private function delete(int $wishlistTargetID): void
    {
        if (
            $wishlistTargetID === 0
            || !Wishlist::instanceByID($wishlistTargetID, $this->db)->isSelfControlled()
        ) {
            return;
        }
        $this->alertService->addNotice(Wishlist::delete($wishlistTargetID), 'deleteWL');
        if ($wishlistTargetID !== $this->wishlistID) {
            return;
        }
        // the currently active one was deleted, search for a new one
        /** @var Wishlist|null $newWishlist */
        $newWishlist = Wishlist::getWishlists()->first();
        if ($newWishlist !== null) {
            $this->wishlistID = $newWishlist->getID();
            $this->alertService->addNotice(
                Wishlist::setDefault($this->wishlistID),
                'setDefaultWL'
            );
            $this->wishlist = $newWishlist->ladeWunschliste($this->wishlistID);
        } else {
            // the only existing wishlist was deleted, create a new one
            $this->wishlist = new Wishlist(0, $this->db);
            $this->wishlist->schreibeDB();
            $this->wishlistID = $this->wishlist->getID();
        }

        $_SESSION['Wunschliste'] = $this->wishlist;
    }

    private function setAsDefault(int $wishlistTargetID): void
    {
        if (
            $wishlistTargetID === 0
            || !Wishlist::instanceByID($wishlistTargetID, $this->db)->isSelfControlled()
        ) {
            return;
        }
        $this->alertService->addNotice(Wishlist::setDefault($wishlistTargetID), 'setDefaultWL');
        $this->wishlistID = $wishlistTargetID;
        $this->wishlist   = Wishlist::instanceByID($this->wishlistID, $this->db);
    }

    private function setDefaultWishlist(Wishlist $filteredWishlist): void
    {
        $this->wishlist   = $filteredWishlist;
        $this->wishlistID = $this->wishlist->getID();
    }

    private function getAction(): string
    {
        $action = Request::pString('action');
        if (!empty($_POST['addToCart'])) {
            $action = 'addToCart';
        } elseif (!empty($_POST['remove'])) {
            $action = 'remove';
        }

        return $action;
    }

    private function handlePostAction(string $searchQuery, string $action): void
    {
        $wishlistTargetID = Request::verifyGPCDataInt('kWunschlisteTarget');
        $wishlistItemID   = 0;
        if (!empty($_POST['addToCart'])) {
            $wishlistItemID = Request::pInt('addToCart');
        } elseif (!empty($_POST['remove'])) {
            $wishlistItemID = Request::pInt('remove');
        }
        $this->wishlistID = Request::pInt('kWunschliste');
        $filteredWishlist = Wishlist::instanceByID($this->wishlistID, $this->db)->filterPositions($searchQuery);
        match ($action) {
            'addToCart'    => $this->addToCart($wishlistItemID, $filteredWishlist),
            'sendViaMail'  => $this->sendViaMail($filteredWishlist),
            'addAllToCart' => $this->addAllToCart($filteredWishlist),
            'remove'       => $this->removeItem($wishlistItemID),
            'removeAll'    => $this->removeAll(),
            'update'       => $this->update($filteredWishlist),
            'setPublic'    => $this->setPublic($wishlistTargetID),
            'setPrivate'   => $this->setPrivate($wishlistTargetID),
            'createNew'    => $this->createNew(),
            'delete'       => $this->delete($wishlistTargetID),
            'setAsDefault' => $this->setAsDefault($wishlistTargetID),
            default        => $this->setDefaultWishlist($filteredWishlist)
        };
    }
}
