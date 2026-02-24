<?php declare(strict_types=1);

namespace Plugin\lfs_shopvote\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20241108000300 extends Migration implements IMigration
{
    /**
     * This migration copies the data from the outdated data structure to the new one
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("
            INSERT IGNORE INTO `xplugin_lfs_shopvote_reviews` (
                `remote_id`, `sku`, `article_id`, `author_name`, `rating_value`, `text`, `created_at`
            )
            SELECT
                `item`.`id`, `artikel`.`cArtNr`,  `review`.`article_id`, `item`.`author`, CAST(`item`.`rating_value` AS DECIMAL(2,1)) AS `rating_value`, `item`.`rating_text`, `item`.`created_at`
            FROM
                `xplugin_lfs_shopvote_article_review_items` AS `item`
                INNER JOIN `xplugin_lfs_shopvote_article_reviews` AS `review` ON (`review`.`id` = `item`.`review_id`)
                INNER JOIN `tartikel` AS `artikel` ON (`artikel`.`kArtikel` = `review`.`article_id`);
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // noop
    }
}