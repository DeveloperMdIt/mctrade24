<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

/**
 * Class Paths
 * @package JTL\Plugin\Data
 */
class Paths
{
    private string $shopURL;

    private string $baseDir;

    private string $basePath;

    private string $versionedPath;

    private string $frontendPath;

    private string $frontendURL;

    private string $baseURL;

    private string $adminPath;

    private string $adminURL;

    private string $backendURL;

    private string $backendRoute;

    private ?string $licencePath = null;

    private ?string $uninstaller = null;

    private ?string $portletsPath = null;

    private ?string $portletsUrl = null;

    private ?string $exportPath = null;

    public function getShopURL(): string
    {
        return $this->shopURL;
    }

    public function setShopURL(string $shopURL): void
    {
        $this->shopURL = $shopURL;
    }

    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    public function setBaseDir(string $baseDir): void
    {
        $this->baseDir = $baseDir;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    public function getVersionedPath(): string
    {
        return $this->versionedPath;
    }

    public function setVersionedPath(string $versionedPath): void
    {
        $this->versionedPath = $versionedPath;
    }

    public function getFrontendPath(): string
    {
        return $this->frontendPath;
    }

    public function setFrontendPath(string $frontendPath): void
    {
        $this->frontendPath = $frontendPath;
    }

    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    public function setBaseURL(string $baseURL): void
    {
        $this->baseURL = $baseURL;
    }

    public function getFrontendURL(): string
    {
        return $this->frontendURL;
    }

    public function setFrontendURL(string $frontendURL): void
    {
        $this->frontendURL = $frontendURL;
    }

    public function getAdminPath(): string
    {
        return $this->adminPath;
    }

    public function setAdminPath(string $adminPath): void
    {
        $this->adminPath = $adminPath;
    }

    public function getAdminURL(): string
    {
        return $this->adminURL;
    }

    public function setAdminURL(string $adminURL): void
    {
        $this->adminURL = $adminURL;
    }

    public function getBackendURL(): string
    {
        return $this->backendURL;
    }

    public function setBackendURL(string $backendURL): void
    {
        $this->backendURL = $backendURL;
    }

    public function setBackendRoute(string $route): void
    {
        $this->backendRoute = $route;
    }

    public function getBackendRoute(): string
    {
        return $this->backendRoute;
    }

    public function getLicencePath(): ?string
    {
        return $this->licencePath;
    }

    public function setLicencePath(string $licencePath): void
    {
        $this->licencePath = $licencePath;
    }

    public function getUninstaller(): ?string
    {
        return $this->uninstaller;
    }

    public function setUninstaller(?string $uninstaller): void
    {
        $this->uninstaller = $uninstaller;
    }

    public function getPortletsPath(): ?string
    {
        return $this->portletsPath;
    }

    public function setPortletsPath(?string $portletsPath): void
    {
        $this->portletsPath = $portletsPath;
    }

    public function getPortletsUrl(): ?string
    {
        return $this->portletsUrl;
    }

    public function setPortletsUrl(?string $portletsUrl): void
    {
        $this->portletsUrl = $portletsUrl;
    }

    public function getExportPath(): ?string
    {
        return $this->exportPath;
    }

    public function setExportPath(?string $exportPath): void
    {
        $this->exportPath = $exportPath;
    }
}
