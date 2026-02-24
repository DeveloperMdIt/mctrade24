<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class ManageBase
 * @package Plugin\jtl_search
 */
abstract class ManageBase
{
    public const STATUS_NOT_DONE = 1;

    public const STATUS_DONE = 2;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @var string|null
     */
    protected ?string $cssFile = null;

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var int
     */
    private int $sort = 0;

    /**
     * @var string|null
     */
    protected ?string $contentTemplate = null;

    /**
     * @var array<string, mixed>
     */
    private array $xContentVarAssoc = [];

    /**
     * @var bool
     */
    private bool $bIssetContent = false;

    /**
     * @var stdClass|null
     */
    protected ?stdClass $serverInfo = null;

    /**
     * ManageBase constructor.
     * @param LoggerInterface $logger
     * @param DbInterface     $db
     * @param stdClass|null   $serverInfo
     */
    abstract public function __construct(LoggerInterface $logger, DbInterface $db, ?stdClass $serverInfo);

    /**
     * @param bool $force
     */
    abstract public function generateContent(bool $force = false): void;

    /**
     * @param int $sort
     * @return $this
     */
    protected function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    protected function setName(string $name): self
    {
        if (\strlen($name) > 0) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * @param string $template
     * @return $this
     */
    protected function setContentTemplate(string $template): self
    {
        if (\strlen($template) > 0) {
            $this->contentTemplate = $template;
        }

        return $this;
    }

    /**
     * @param string $file
     * @return $this
     */
    protected function setCssFile(string $file): self
    {
        if (\strlen($file) > 0) {
            $this->cssFile = $file;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $var
     * @return $this
     */
    protected function setContentVar(string $key, mixed $var): self
    {
        if (isset($var) && \strlen($key) > 0) {
            $this->xContentVarAssoc[$key] = $var;
        }

        return $this;
    }

    /**
     * @return int
     */
    final public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @return array|null
     */
    public function getContent(): ?array
    {
        $this->generateContent();
        if (\strlen($this->contentTemplate) > 0 && \is_file($this->contentTemplate)) {
            $xResult                     = [];
            $xResult['cTemplate']        = $this->contentTemplate;
            $xResult['xContentVarAssoc'] = $this->xContentVarAssoc;

            return $xResult;
        }

        return null;
    }

    /**
     * @return null|string
     */
    final public function getCssURL(): ?string
    {
        return $this->cssFile;
    }

    /**
     * @return null|string
     */
    final public function getName(): ?string
    {
        if (\strlen($this->name) > 0) {
            return $this->name;
        }

        return null;
    }

    /**
     * @param bool $issetContent
     * @return $this
     */
    final public function setIssetContent(bool $issetContent): self
    {
        $this->bIssetContent = $issetContent;

        return $this;
    }

    /**
     * @return bool
     */
    final public function getIssetContent(): bool
    {
        return $this->bIssetContent;
    }
}
