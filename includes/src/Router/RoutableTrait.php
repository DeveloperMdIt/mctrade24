<?php

declare(strict_types=1);

namespace JTL\Router;

use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Trait RoutableTrait
 * @package JTL\Router
 */
trait RoutableTrait
{
    protected int $currentLanguageID;

    protected string $currentLocale;

    protected int $fallbackLanguageID;

    /**
     * @var array<int, string>
     */
    protected array $urls = [];

    /**
     * @var array<int, string>
     */
    protected array $paths = [];

    /**
     * @var array<int, string>
     */
    protected array $slugs = [];

    protected string $routeType;

    public function getCurrentLanguageID(): int
    {
        return $this->currentLanguageID;
    }

    public function setCurrentLanguageID(int $currentLanguageID): void
    {
        $this->currentLanguageID = $currentLanguageID;
    }

    /**
     * @inheritdoc
     */
    public function getRouteType(): string
    {
        return $this->routeType;
    }

    /**
     * @inheritdoc
     */
    public function setRouteType(string $routeType): void
    {
        $this->routeType = $routeType;
    }

    /**
     * @inheritdoc
     */
    public function initLanguageID(?int $languageID = null, ?string $locale = null): void
    {
        if ($languageID === 0) {
            $languageID = null;
        }
        $this->currentLanguageID = $languageID ?? Shop::getLanguageID();
        if ($this->currentLanguageID === 0) {
            $this->currentLanguageID = (int)($_SESSION['kSprache'] ?? 1);
        }
        $this->fallbackLanguageID = LanguageHelper::getDefaultLanguage()->getId();
        $this->currentLocale      = Text::convertISO2ISO639($locale ?? Shop::getLanguageCode());
    }

    /**
     * @inheritdoc
     */
    public function createBySlug(?int $fallbackID = null, array $additional = []): void
    {
        $router    = Shop::getRouter();
        $languages = LanguageHelper::getAllLanguages();
        foreach ($this->getSlugs() as $langID => $slug) {
            foreach ($languages as $languageModel) {
                if ($languageModel->getId() !== $langID) {
                    continue;
                }
                $locale = $languageModel->getIso639();
                $route  = $router->getPathByType(
                    $this->getRouteType(),
                    \array_merge(['lang' => $locale, 'name' => $slug, 'id' => $fallbackID], $additional)
                );
                $this->setURLPath($route, $langID);
                $url = $router->getURLByType(
                    $this->getRouteType(),
                    \array_merge(['lang' => $locale, 'name' => $slug, 'id' => $fallbackID], $additional)
                );
                $this->setURL($url, $langID);
                break;
            }
        }
        if ($fallbackID === null || \count($this->slugs) >= \count($languages)) {
            return;
        }
        foreach ($languages as $languageModel) {
            $langID = $languageModel->getId();
            if (\array_key_exists($langID, $this->slugs)) {
                continue;
            }
            $route = $router->getPathByType(
                $this->getRouteType(),
                \array_merge(['lang' => $languageModel->getIso639(), 'id' => $fallbackID], $additional)
            );
            $this->setURLPath($route, $langID);
            $url = $router->getURLByType(
                $this->getRouteType(),
                \array_merge(['lang' => $languageModel->getIso639(), 'id' => $fallbackID], $additional)
            );
            if (!$languageModel->isShopDefault() && !\str_contains($url, '?')) {
                $url .= '?lang=' . $languageModel->getCode();
            }
            $this->setURL($url, $langID);
        }
    }

    /**
     * @inheritdoc
     */
    public function getURLs(): array
    {
        return $this->urls;
    }

    /**
     * @inheritdoc
     */
    public function setURLs(array $urls): void
    {
        $this->urls = $urls;
    }

    /**
     * @inheritdoc
     */
    public function getURL(?int $idx = null): ?string
    {
        return $this->urls[$idx ?? $this->currentLanguageID] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getURLPaths(): array
    {
        return $this->paths;
    }

    /**
     * @inheritdoc
     */
    public function setURLPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @inheritdoc
     */
    public function getURLPath(?int $idx = null): ?string
    {
        return $this->paths[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setURLPath(string $path, ?int $idx = null): void
    {
        $this->paths[$idx ?? $this->currentLanguageID] = $path;
    }

    /**
     * @inheritdoc
     */
    public function setURL(string $url, ?int $idx = null): void
    {
        $this->urls[$idx ?? $this->currentLanguageID] = $url;
    }

    /**
     * @inheritdoc
     */
    public function getSlugs(): array
    {
        return $this->slugs;
    }

    /**
     * @inheritdoc
     */
    public function getSlug(?int $idx = null): string
    {
        return $this->slugs[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function setSlug(string $seo, ?int $idx = null): void
    {
        $this->slugs[$idx ?? $this->currentLanguageID] = $seo;
    }
}
