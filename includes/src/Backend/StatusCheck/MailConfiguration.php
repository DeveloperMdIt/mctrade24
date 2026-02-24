<?php

declare(strict_types=1);

namespace JTL\Backend\StatusCheck;

use JTL\Backend\NotificationEntry;
use JTL\Router\Route;
use JTL\Shop;

class MailConfiguration extends AbstractStatusCheck
{
    protected bool $includeInServiceReport = true;

    protected int $messageType = NotificationEntry::TYPE_DANGER;

    private string $hash = '';

    public function isOK(): bool
    {
        $conf       = Shop::getSettingSection(\CONF_EMAILS);
        $this->hash = \md5('hasInsecureMailConfig_' . $conf['email_methode']);

        return $conf['email_methode'] !== 'smtp' || !empty(\trim($conf['email_smtp_verschluesselung']));
    }

    public function getURL(): ?string
    {
        return $this->adminURL . Route::CONFIG . '?kSektion=3';
    }

    public function getTitle(): string
    {
        return \__('hasInsecureMailConfigTitle');
    }

    public function generateMessage(): void
    {
        $this->addNotification(\__('hasInsecureMailConfigMessage'), $this->hash);
    }
}
