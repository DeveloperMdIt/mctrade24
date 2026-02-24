<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JsonException;
use Plugin\s360_clerk_shop5\src\Utils\LoggerTrait;
use RuntimeException;

/**
 * The JsonFeedStreamWriter class can be used to write objects part by part instead of all at once.
 *
 * This is useful because it uses much less memory for larger objects.
 *
 * @package Plugin\s360_clerk_shop5\src\Export
 */
class JsonFeedStreamWriter
{
    use LoggerTrait;

    private $hasErrors = false;
    private const COLLECTION_MODE = 'COLLECTION';
    private const OBJECT_MODE = 'OBJECT';

    /**
     * @var resource The datafeed file
     */
    private $resource;

    /**
     * @var null|string The current content mode - can either be COLLECTION or OBJECT or null
     */
    private ?string $mode = null;

    /**
     * @var bool Flag to check whether content as been written for the current mode
     */
    private bool $contentWasWritten = false;

    /**
     * @var null|string The current object property key
     */
    private ?string $currentKey = null;

    /**
     * @param string $path Path to the feed (creates new file)
     * @throws RuntimeException if the feed file cannot be opened for whatever reason
     */
    public function __construct(string $path)
    {
        file_put_contents($path, '');
        $this->resource = fopen($path, 'wb');

        if ($this->resource === false) {
            throw new RuntimeException('Could not open feed: ' . $path);
        }

        if (!flock($this->resource, LOCK_EX)) {
            throw new RuntimeException('Could not get write access to feed: ' . $path);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close the data feed if not closed
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->resource)) {
            if (!$this->contentWasWritten) {
                fwrite($this->resource, "null");
            }

            if ($this->mode === self::COLLECTION_MODE) {
                $this->endCollection();
            }

            if ($this->mode === self::OBJECT_MODE && !empty($this->currentKey)) {
                fwrite($this->resource, "}");
            }

            flock($this->resource, LOCK_UN);
            fclose($this->resource);
        }
    }

    /**
     * Start collection mode
     * @return self
     */
    public function startCollection(): self
    {
        $this->mode = self::COLLECTION_MODE;
        fwrite($this->resource, '[');

        return $this;
    }

    /**
     * End collection mode.
     *
     * Has to be called in order to close the collection, ie write `]`
     *
     * @return self
     */
    public function endCollection(): self
    {
        fwrite($this->resource, ']');
        $this->contentWasWritten = true;
        $this->mode = null;

        return $this;
    }

    /**
     * Add object property to feed
     * @param string $property
     * @return JsonFeedStreamWriter
     */
    public function addProperty(string $property): self
    {
        // close collection if it was not closed yet
        if ($this->mode === self::COLLECTION_MODE) {
            $this->endCollection();
        }

        // Start (root) object
        $this->mode = self::OBJECT_MODE;
        if (empty($this->currentKey)) {
            fwrite($this->resource, "{");
        }

        // if there was already a property, write "null" value if no content was written
        if ($this->currentKey !== null) {
            if (!$this->contentWasWritten) {
                fwrite($this->resource, "null");
            }

            fwrite($this->resource, ',');
        }

        // Write new property with no content
        $this->currentKey = $property;
        $this->contentWasWritten = false;
        fwrite($this->resource, '"' . $property . '":');

        return $this;
    }

    /**
     * Set the value for an object property (does not work in collection mode)
     * @param mixed $content
     * @return JsonFeedStreamWriter
     */
    public function setValue(mixed $content): self
    {
        // Try to encode data, in case of error log them and ignore this row!
        try {
            $data = json_encode($content, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (JsonException $exc) {
            $this->noticeLog(
                "Encountered the following JSON error \"{$exc->getMessage()}\" while trying to write the row for: "
                . print_r($content, true)
            );

            $this->hasErrors = true;

            return $this;
        }

        if ($this->mode === self::OBJECT_MODE) {
            $this->contentWasWritten = true;
            fwrite($this->resource, $data);
        }

        return $this;
    }

    /**
     * Push an item to the collection
     *
     * Automatically starts the collection mode if not done yet
     *
     * @param mixed $item
     * @return JsonFeedStreamWriter
     */
    public function push(mixed $item): self
    {
        // Try to encode data, in case of error log them and ignore this row!
        try {
            $data = json_encode($item, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (JsonException $exc) {
            $this->noticeLog(
                "Encountered the following JSON error \"{$exc->getMessage()}\" while trying to write the row for: "
                . print_r($item, true)
            );

            $this->hasErrors = true;

            return $this;
        }

        if ($this->mode !== self::COLLECTION_MODE) {
            $this->startCollection();
        }

        if ($this->contentWasWritten) {
            fwrite($this->resource, ',');
        }

        fwrite($this->resource, $data);
        $this->contentWasWritten = true;

        return $this;
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }
}
