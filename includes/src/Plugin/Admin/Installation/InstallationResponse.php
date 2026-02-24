<?php

declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation;

use stdClass;

/**
 * Class InstallationResponse
 * @package JTL\Plugin\Admin\Installation
 */
class InstallationResponse
{
    public const STATUS_OK = 'OK';

    public const STATUS_FAILED = 'FAILED';

    private string $status = self::STATUS_OK;

    private ?string $errorMessage;

    private ?string $dir_name;

    private ?string $path;

    /**
     * @var string[]
     */
    private array $files_unpacked = [];

    /**
     * @var string[]
     */
    private array $files_failed = [];

    /**
     * @var string[]
     */
    private array $messages = [];

    private stdClass $html;

    private ?string $license;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): InstallationResponse
    {
        $this->status = $status;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->errorMessage;
    }

    public function setError(?string $errorMessage): InstallationResponse
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getDirName(): ?string
    {
        return $this->dir_name;
    }

    public function setDirName(?string $dir_name): InstallationResponse
    {
        $this->dir_name = $dir_name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): InstallationResponse
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFilesUnpacked(): array
    {
        return $this->files_unpacked;
    }

    /**
     * @param string[] $files_unpacked
     */
    public function setFilesUnpacked(array $files_unpacked): InstallationResponse
    {
        $this->files_unpacked = $files_unpacked;

        return $this;
    }

    public function addFileUnpacked(string $file): InstallationResponse
    {
        $this->files_unpacked[] = $file;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFilesFailed(): array
    {
        return $this->files_failed;
    }

    /**
     * @param string[] $files_failed
     * @return InstallationResponse
     */
    public function setFilesFailed(array $files_failed): InstallationResponse
    {
        $this->files_failed = $files_failed;

        return $this;
    }

    public function addFileFailed(string $file): InstallationResponse
    {
        $this->files_failed[] = $file;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param string[] $messages
     * @return InstallationResponse
     */
    public function setMessages(array $messages): InstallationResponse
    {
        $this->messages = $messages;

        return $this;
    }

    public function addMessage(string $message): InstallationResponse
    {
        $this->messages[] = $message;

        return $this;
    }

    public function getHtml(): stdClass
    {
        return $this->html;
    }

    public function setHtml(stdClass $html): InstallationResponse
    {
        $this->html = $html;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): void
    {
        $this->license = $license;
    }

    /**
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return \json_encode(\get_object_vars($this), JSON_THROW_ON_ERROR) ?: '';
    }
}
