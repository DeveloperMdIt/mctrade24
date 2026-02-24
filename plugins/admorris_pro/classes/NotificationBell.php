<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use JTL\Shop; use JTL\Backend\Notification; use JTL\Backend\NotificationEntry; class NotificationBell { public function __construct(private $plugin) { } public function setNotification(?string $message = '', ?string $link = '', ?int $type = null) : void { $notificationHelper = Notification::getInstance(Shop::Container()->getDB()); $entry = new NotificationEntry($type ?? NotificationEntry::TYPE_WARNING, \__($this->plugin->getMeta()->getName()), \__($message), $link); $entry->setPluginId($this->plugin->getPluginID()); $notificationHelper->addNotify($entry); } }