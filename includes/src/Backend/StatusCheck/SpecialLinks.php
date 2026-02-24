<?php

declare(strict_types=1);

namespace JTL\Backend\StatusCheck;

use JTL\Backend\NotificationEntry;
use JTL\Link\Link;
use JTL\Router\Route;
use JTL\Shop;

class SpecialLinks extends AbstractStatusCheck
{
    protected int $messageType = NotificationEntry::TYPE_DANGER;

    public function isOK(): bool
    {
        $group = Shop::Container()->getLinkService()->getAllLinkGroups()->getLinkgroupByTemplate('specialpages');
        if ($group === null) {
            return true;
        }
        $count = $group->getLinks()->filter(
            static function (Link $link): bool {
                return $link->hasDuplicateSpecialLink();
            }
        )->count();

        return $count === 0;
    }

    public function getURL(): ?string
    {
        return $this->adminURL . Route::LINKS;
    }

    public function getTitle(): string
    {
        return \__('duplicateSpecialLinkTitle');
    }

    public function generateMessage(): void
    {
        $this->addNotification(\__('duplicateSpecialLinkDesc'));
    }
}
