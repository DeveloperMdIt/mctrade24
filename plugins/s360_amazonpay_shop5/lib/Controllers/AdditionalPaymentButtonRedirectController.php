<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;


use JTL\Alert\Alert;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Frontend\Button;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;

/**
 * Class AdditionalPaymentButtonRedirectController
 *
 * This controller is responsible for redirecting the customer to Amazon Pay if they use the Amazon Pay Additional Payment Button solution. (I.e. they selected Amazon Pay within the regular checkout.)
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class AdditionalPaymentButtonRedirectController {

    use JtlLoggerTrait;

    protected $plugin;

    /**
     * @var array $request
     */
    protected $request;

    public function __construct(PluginInterface  $plugin) {
        $this->plugin = $plugin;
        $this->request = Text::filterXSS($_REQUEST);
    }

    /**
     * @throws \SmartyException
     * @throws \Exception
     */
    public function handle(): void {
        // Avoid ajax calls to this site altogether, special handling for dropper because it does not use regular ajax requests
        if (!empty($this->request['x-dropper-ajax-request']) || Request::isAjaxRequest()) {
            return;
        }

        $order = SessionController::get(SessionController::KEY_APB_ORDER);
        if(empty($order)) {
            // ERROR
            $this->debugLog('Additional Payment Button Redirect Controller was called without order object in Amazon session - probably an out of band reload or direct call to the URL.');
            // We ignore this request completely and just return nothing. The template will display an according error *if it gets displayed*.
            return;
        }

        // Prevent reloads on this controller and clear any remaining checkout session data from previous Amazon Pay interaction - this checkout session data will be re-gained by the result controller when it encounters this situation.
        SessionController::clear(SessionController::KEY_APB_ORDER);
        SessionController::clearAllCheckoutSessions();


        $button = new Button(Button::TYPE_PAY, Button::CONTEXT_APB_REDIRECT);
        try {
            $button->setAdditionalPaymentButtonPayload($order);
        } catch(\Exception $e) {
            $this->debugLog('Additional Payment Button Payload could not be created: ' . $e->getMessage());
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_ERROR_REDIRECT), 'lpaApbButtonException', ['dismissable' => true, 'saveInSession' => true]);
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php', true, true) . '?editZahlungsart=1');
            exit();
        }
        $buttonHtml = $button->render();
        $vars = [
            'buttonHtml' => $buttonHtml
        ];
        Shop::Smarty()->assign('lpaApb', $vars);
    }
}