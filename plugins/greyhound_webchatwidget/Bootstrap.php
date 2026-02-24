<?php

// Copyright (C) 2024 GREYHOUND Software (https://greyhound-software.com)
// All Rights Reserved.
//
// This is PROPRIETARY SOURCE CODE of GREYHOUND Software;
// the contents of this file may not be disclosed to third parties, copied or
// duplicated in any form, in whole or in part, without the prior written
// permission of GREYHOUND Software.

declare(strict_types=1);

namespace Plugin\greyhound_webchatwidget;

use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use JTL\Consent\Item;
use JTL\Smarty\JTLSmarty;
use JTL\Session\Frontend;
use JTL\Shop;


class Bootstrap extends Bootstrapper
{
    public function boot(Dispatcher $dispatcher)
    {
        parent::boot($dispatcher);

		$plugin  = $this->getPlugin();
        $consentNeeded = $plugin->getConfig()->getValue('greyhound_webchatwidget_consent') === 'Y' ? true : false;
        $pluginActiveSetting = $plugin->getConfig()->getValue('greyhound_webchatwidget_active');
        $widgetToken = $plugin->getConfig()->getValue('greyhound_webchatwidget_token');

        if($pluginActiveSetting == "on" && $widgetToken != "")
        {
            if ($consentNeeded)
                $dispatcher->listen('shop.hook.' . \CONSENT_MANAGER_GET_ACTIVE_ITEMS, [$this, 'addConsentItem']);

            $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_INC, function (array $args) use ($consentNeeded, $widgetToken) {
                /** @var JTLSmarty $smarty */
                $smarty = $args['smarty'];
                $smarty->assign('ghctoken', $widgetToken);
                $smarty->assign('consentNeeded', $consentNeeded ? 'true' : 'false');
            });
        }
	}

    /**
     * @inheritdoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $smarty->assign('ghctoken', $this->getPlugin()->getConfig()->getValue('greyhound_webchatwidget_token'));
        $smarty->assign('ghcapi', $this->getPlugin()->getConfig()->getValue('greyhound_webchatwidget_api'));

        return $smarty->fetch($this->getPlugin()->getPaths()->getAdminPath() . '/template/welcome.tpl');
    }

    /**
     * @param array $args
     */
    public function addConsentItem(array $args): void
    {
        $lastID = $args['items']->reduce(static function ($result, Item $item) {
            $value = $item->getID();

            return $result === null || $value > $result ? $value : $result;
        }) ?? 0;
        $item   = new Item();
        $item->setName('Service via Webchat Messenger');
        $item->setID(++$lastID);
        $item->setItemID('greyhound_webchatwidget_consent');
        $item->setDescription('Um den Webchat-Service auf dieser Seite nutzen zu können, müssen Sie der Speicherung von Sitzungsdaten im Session Storage der Website zustimmen. Diese Daten dienen lediglich dazu, Ihre Anfrage beim Durchsuchen dieses Shops nicht zu verlieren. Ohne Ihre Zustimmung findet keine Datenspeicherung statt, jedoch können die Funktionen des Webchat-Services auf dieser Seite dann auch nicht verwendet werden.');
        $item->setPurpose('Service via Webchat Messenger');
        $item->setPrivacyPolicy('https://docs.greyhound-software.com/konfiguration/messaging#datenschutz-informationen');
        $item->setCompany('GREYHOUND Software GmbH');
        $args['items']->push($item);
    }
}

