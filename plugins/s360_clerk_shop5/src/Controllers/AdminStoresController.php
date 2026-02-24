<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Controllers;

use JTL\Alert\Alert;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\s360_clerk_shop5\src\Entities\StoreEntity;
use Plugin\s360_clerk_shop5\src\Export\FeedGenerator;
use Plugin\s360_clerk_shop5\src\Models\StoreModel;
use Plugin\s360_clerk_shop5\src\Utils\Helpers;

final class AdminStoresController extends Controller
{
    public const TABNAME = 'Stores';
    public const LISTING_TEMPLATE = 'template/stores/listing';
    public const SETTINGS_TEMPLATE = 'template/stores/settings';

    private StoreModel $model;
    private Helpers $helpers;

    public function __construct(
        PluginInterface $plugin,
        JTLSmarty $smarty,
        AlertServiceInterface $alertService
    ) {
        parent::__construct($plugin, $smarty, $alertService);

        $this->model = new StoreModel();
        $this->helpers = new Helpers($this->plugin);
    }

    public function handle(): string
    {
        $action = Request::getVar('action', 'index');

        if ($action === 'settings') {
            return $this->settingsAction();
        }

        if ($action === 'add') {
            return $this->addAction();
        }

        if (Request::postInt('refresh') && Form::validateToken()) {
            $generator = new FeedGenerator();
            $generator->createFeed($this->model->find(Request::postInt('refresh')));
        }

        if (Request::postInt('delete') && Form::validateToken()) {
            return $this->deleteAction(Request::postInt('delete'));
        }

        $this->smarty->assign('s360_clerk_store', [
            'helpers' => $this->helpers,
            'tabname' => self::TABNAME,
            'items' => $this->model->all()
        ]);

        return $this->smarty->fetch($this->getTemplate(self::LISTING_TEMPLATE, self::TEMPLATE_TYPE_BACKEND));
    }

    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function addAction(): string
    {
        if (Request::postInt('saving') && Form::validateToken()) {
            $id = $this->model->insert([
                'api_key' => Request::postVar('api_key'),
                'private_key' => Request::postVar('private_key'),
                'lang_id' => Request::postVar('language'),
                'customer_group' => Request::postVar('customer_group'),
            ]);

            $store = $this->model->find($id);

            if (empty($store)) {
                $this->alertService->addAlert(
                    Alert::TYPE_ERROR,
                    __('Feed konnte nicht gespeicher werden'),
                    'settings-error',
                    ['saveInSession' => true]
                );
                header('Location: ' . $this->helpers->getFullAdminTabUrl(self::TABNAME));
                exit;
            }

            $this->saveSettings($store);
        }

        $this->smarty->assign('s360_clerk_store', [
            'helpers' => $this->helpers,
            'tabname' => self::TABNAME,
            'currencies' => $this->helpers->loadAllCurrencies(),
            'customer_groups' => CustomerGroup::getGroups(),
            'languages' => LanguageHelper::getAllLanguages(),
        ]);

        return $this->smarty->fetch($this->getTemplate(self::SETTINGS_TEMPLATE, self::TEMPLATE_TYPE_BACKEND));
    }

    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function settingsAction(): string
    {
        $store = $this->model->find(Request::getInt('id'));

        if (empty($store)) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                __('Feed konnte nicht gefunden werden'),
                'settings-error',
                ['saveInSession' => true]
            );
            header('Location: ' . $this->helpers->getFullAdminTabUrl(self::TABNAME));
            exit;
        }

        if (Request::postInt('saving') && Form::validateToken()) {
            $this->saveSettings($store);
        }

        $this->smarty->assign('s360_clerk_store', [
            'helpers' => $this->helpers,
            'tabname' => self::TABNAME,
            'currencies' => $this->helpers->loadAllCurrencies(),
            'customer_groups' => CustomerGroup::getGroups(),
            'languages' => LanguageHelper::getAllLanguages(),
            'feed' => $this->model->find(Request::getInt('id'))
        ]);

        return $this->smarty->fetch($this->getTemplate(self::SETTINGS_TEMPLATE, self::TEMPLATE_TYPE_BACKEND));
    }

    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @return never
     */
    private function deleteAction(int $id)
    {
        try {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                __('Der Eintrag wurde erfolgreich gelöscht.'),
                'delete-success',
                ['saveInSession' => true]
            );

            $this->model->delete($id);
        } catch (\Exception $exc) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                $exc->getMessage(),
                'delete-error',
                ['saveInSession' => true]
            );
        }

        header('Location: ' . $this->helpers->getFullAdminTabUrl(self::TABNAME));
        exit;
    }

    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @return never
     */
    private function saveSettings(StoreEntity $store)
    {
        $settings = Request::postVar('setting');
        Shop::Container()->getDB()->delete('xplugin_s360_clerk_shop5_store_settings', 'store_id', $store->getId());

        foreach ($settings as $key => $value) {
            Shop::Container()->getDB()->upsert(
                'xplugin_s360_clerk_shop5_store_settings',
                (object) ['store_id' => $store->getId(), 'key' => $key, 'value' => $value]
            );
        }

        $store->setApiKey(Request::postVar('api_key'));
        $store->setPrivateKey(Request::postVar('private_key'));
        $store->setLanguageId(Request::postInt('language'));
        $store->setCustomerGroupId(Request::postInt('customer_group'));
        $this->model->update($store->getId(), $store);

        $this->alertService->addAlert(
            Alert::TYPE_SUCCESS,
            __('Die Einstellungen wurden erfolgreich gespeichert.'),
            'save-success',
            ['saveInSession' => true]
        );
        header('Location: ' . $this->helpers->getFullAdminTabUrl(self::TABNAME));
        exit;
    }
}
