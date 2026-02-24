<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Entities;

use DateTime;
use JTL\Customer\CustomerGroup;
use JTL\Language\LanguageModel;
use JTL\Shop;

class StoreEntity extends Entity
{

    private LanguageModel $language;
    private CustomerGroup $customerGroup;
    private ?StoreSettingsEntity $settings = null;

    public function __construct(
        private int $id,
        private int $languageId,
        private int $customerGroupId,
        private ?string $apiKey,
        private ?string $privateKey,
        private ?string $state,
        private ?string $stateMessage,
        private DateTime $createdAt,
        private ?DateTime $updatedAt,
    ) {
        $this->language      = Shop::Lang()->getLanguageByID($languageId);
        $this->customerGroup = new CustomerGroup($customerGroupId);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            (int) $data['lang_id'],
            (int) $data['customer_group'],
            $data['api_key'] ? (string) $data['api_key'] : null,
            $data['private_key'] ? (string) $data['private_key'] : null,
            $data['state'] ? (string) $data['state'] : null,
            $data['state_message'] ? (string) $data['state_message'] : null,
            $data['created_at'] ? DateTime::createFromFormat('Y-m-d H:i:s', $data['created_at']) : null,
            $data['updated_at'] ? DateTime::createFromFormat('Y-m-d H:i:s', $data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->getId(),
            'lang_id'        => $this->getLanguageId(),
            'customer_group' => $this->getCustomerGroupId(),
            'api_key'        => $this->getApiKey(),
            'private_key'    => $this->getPrivateKey(),
            'state'          => $this->getState(),
            'state_message'  => $this->getStateMessage(),
            'created_at'     => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at'     => $this->getUpdatedAt()?->format('Y-m-d H:i:s')
        ];
    }

    public function getHash(): string
    {
        return md5(BLOWFISH_KEY . $this->getId() . $this->getLanguageId() . $this->getCustomerGroupId());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
    }

    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId;
    }

    public function setCustomerGroupId(int $customerGroupId): self
    {
        $this->customerGroupId = $customerGroupId;
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(?string $privateKey): self
    {
        $this->privateKey = $privateKey;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getStateMessage(): ?string
    {
        return $this->stateMessage;
    }

    public function setStateMessage(?string $stateMessage): self
    {
        $this->stateMessage = $stateMessage;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLanguage(): LanguageModel
    {
        return $this->language;
    }

    public function setLanguage(LanguageModel $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getCustomerGroup(): CustomerGroup
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(CustomerGroup $customerGroup): self
    {
        $this->customerGroup = $customerGroup;
        return $this;
    }

    public function getSettings(): ?StoreSettingsEntity
    {
        return $this->settings;
    }

    public function setSettings(StoreSettingsEntity $settings): self
    {
        $this->settings = $settings;
        return $this;
    }
}
