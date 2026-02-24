<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Models;

use JTL\DB\ReturnType;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Entities\StoreEntity;
use Plugin\s360_clerk_shop5\src\Entities\StoreSettingsEntity;

/**
 * @method ?StoreEntity find(int $id)
 * @method StoreEntity[] all(array $where = [], string $orderBy = '')
 * @package Plugin\s360_clerk_shop5\src\Models
 */
class StoreModel extends Model
{
    public function getTableName(): string
    {
        return 'xplugin_s360_clerk_shop5_store';
    }

    /**
     * Get the current store for the frontend if available.
     *
     * @param null|int $customerGroupId
     * @param null|int $languageId
     * @return null|StoreEntity
     */
    public function getCurrentStore(?int $customerGroupId = null, ?int $languageId = null): ?StoreEntity
    {
        $customerGroupId = $customerGroupId ?? Frontend::getCustomerGroup()->getID();
        $languageId = $languageId ?? Shop::getLanguageID();

        $row = $this->database->select(
            $this->getTableName(),
            'customer_group',
            $customerGroupId,
            'lang_id',
            $languageId
        );

        if (empty($row)) {
            return null;
        }

        return $this->loadSettings($this->getEntity((array) $row));
    }

    /**
     * Load Feed Settings
     * @param StoreEntity $store
     * @return StoreEntity
     */
    public function loadSettings(StoreEntity $store): StoreEntity
    {
        $settings = $this->database->executeQueryPrepared(
            'SELECT * FROM xplugin_s360_clerk_shop5_store_settings WHERE store_id = :id',
            ['id' => $store->getId()],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $settings = array_column($settings, 'value', 'key');

        $store->setSettings(StoreSettingsEntity::fromArray($settings));
        return $store;
    }

    public function getByHash(string $hash): ?StoreEntity
    {
        $row = $this->database->queryPrepared(
            "SELECT * FROM {$this->getTableName()} WHERE MD5(CONCAT(:salt, id, lang_id, customer_group)) = :hash LIMIT 1",
            ['hash' => $hash, 'salt' => BLOWFISH_KEY],
            ReturnType::SINGLE_ASSOC_ARRAY
        );

        if (empty($row)) {
            return null;
        }

        return $this->loadSettings($this->getEntity((array) $row));
    }

    public function getBy(string $key, $value): ?StoreEntity
    {
        /** @var StoreEntity|null $entity */
        $entity = parent::getBy($key, $value);

        if ($entity) {
            $this->loadSettings($entity);
        }

        return $entity;
    }

    public function getEntity(array $data): StoreEntity
    {
        return StoreEntity::fromArray($data);
    }
}
