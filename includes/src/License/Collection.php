<?php

declare(strict_types=1);

namespace JTL\License;

use JTL\License\Struct\ExsLicense;
use JTL\License\Struct\License;

/**
 * Class Collection
 * @package JTL\License
 */
class Collection extends \Illuminate\Support\Collection
{
    /**
     * @return Collection<ExsLicense>
     */
    public function getActive(): self
    {
        return $this->getBound();
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getBound(): self
    {
        return $this->filter(static function (ExsLicense $e): bool {
            return $e->getState() === ExsLicense::STATE_ACTIVE;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getUnbound(): self
    {
        return $this->filter(static function (ExsLicense $e): bool {
            return $e->getState() === ExsLicense::STATE_UNBOUND;
        });
    }

    public function getForItemID(string $itemID): ?ExsLicense
    {
        $matches = $this->getBound()->filter(static function (ExsLicense $e) use ($itemID): bool {
            return $e->getID() === $itemID;
        })->sort(static function (ExsLicense $e): int {
            return $e->getLicense()->getType() === License::TYPE_PROD ? -1 : 1;
        });
        if ($matches->count() > 1) {
            foreach ($matches as $exs) {
                $license = $exs->getLicense();
                if ($license->isExpired() === false && $license->getSubscription()->isExpired() === false) {
                    return $exs;
                }
            }
        }

        return $matches->first();
    }

    public function getForExsID(string $exsID): ?ExsLicense
    {
        $matches = $this->getBound()->filter(static function (ExsLicense $e) use ($exsID): bool {
            return $e->getExsID() === $exsID;
        })->sort(static function (ExsLicense $e): int {
            return $e->getLicense()->getType() === License::TYPE_PROD ? -1 : 1;
        });
        if ($matches->count() > 1) {
            // when there are multiple bound exs licenses, try to choose one that isn't expired yet
            /** @var ExsLicense $exs */
            foreach ($matches as $exs) {
                $license = $exs->getLicense();
                if ($license->isExpired() === false && $license->getSubscription()->isExpired() === false) {
                    return $exs;
                }
            }
        }

        return $matches->first();
    }

    public function getForLicenseKey(string $licenseKey): ?ExsLicense
    {
        return $this->first(static function (ExsLicense $e) use ($licenseKey): bool {
            return $e->getLicense()->getKey() === $licenseKey;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getActiveExpired(): self
    {
        return $this->getBoundExpired()->filter(static function (ExsLicense $e): bool {
            $ref = $e->getReferencedItem();

            return $ref !== null && $ref->isActive();
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getDedupedActiveExpired(): self
    {
        return $this->getActiveExpired()->filter(function (ExsLicense $e): bool {
            return $e === $this->getForExsID($e->getExsID());
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getBoundExpired(): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e): bool {
            $ref = $e->getReferencedItem();

            return $ref !== null
                && ($e->getLicense()->isExpired() || $e->getLicense()->getSubscription()->isExpired());
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getLicenseViolations(): self
    {
        return $this->getDedupedActiveExpired()->filter(static function (ExsLicense $e): bool {
            return !$e->canBeUsed();
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getExpiredActiveTests(): self
    {
        return $this->getExpiredBoundTests();
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getExpiredBoundTests(): self
    {
        return $this->getBoundExpired()->filter(static function (ExsLicense $e): bool {
            return $e->getLicense()->getType() === License::TYPE_TEST;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getDedupedExpiredBoundTests(): self
    {
        return $this->getExpiredBoundTests()->filter(function (ExsLicense $e): bool {
            return $e === $this->getForExsID($e->getExsID());
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getPlugins(): self
    {
        return $this->filter(static function (ExsLicense $e): bool {
            return $e->getType() === ExsLicense::TYPE_PLUGIN || $e->getType() === ExsLicense::TYPE_PORTLET;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getTemplates(): self
    {
        return $this->filter(static function (ExsLicense $e): bool {
            return $e->getType() === ExsLicense::TYPE_TEMPLATE;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getPortlets(): self
    {
        return $this->filter(static function (ExsLicense $e): bool {
            return $e->getType() === ExsLicense::TYPE_PORTLET;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getInstalled(): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e): bool {
            return $e->getReferencedItem() !== null;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getUpdateableItems(): self
    {
        return $this->getBound()->getInstalled()->filter(static function (ExsLicense $e): bool {
            return $e->getReferencedItem()?->hasUpdate() === true;
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getExpired(): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e): bool {
            return $e->getLicense()->isExpired() || $e->getLicense()->getSubscription()->isExpired();
        });
    }

    /**
     * @return Collection<ExsLicense>
     */
    public function getAboutToBeExpired(int $days = 28): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e) use ($days): bool {
            $license = $e->getLicense();

            return (!$license->isExpired()
                    && $license->getDaysRemaining() > 0
                    && $license->getDaysRemaining() < $days)
                || (!$license->getSubscription()->isExpired()
                    && $license->getSubscription()->getDaysRemaining() > 0
                    && $license->getSubscription()->getDaysRemaining() < $days
                );
        });
    }
}
