<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Utils;

use JTL\Shop;
use stdClass;

final class Config
{
    public const PLUGIN_ID = 's360_clerk_shop5';
    public const PAGE_SEARCH_RESULTS = 'clerkSearchResults';

    public const SETTING_COOKIELESS_TRACKING = 'use_cookieless_tracking';
    public const SETTING_CART_TRACKING = 'use_cart_tracking';
    public const SETTING_HASHED_MAILS = 'use_hashed_mails';
    public const SETTING_LIVESEARCH_ACTIVE = 'livesearch_active';
    public const SETTING_LIVESEARCH_SELECTOR = 'livesearch_selector';
    public const SETTING_LIVESEARCH_TEMPLATE = 'livesearch_template';
    public const SETTING_COUNT_SEARCH_SUGGESTIONS = 'count_search_suggestions';
    public const SETTING_COUNT_CATEGORY_SUGGESTIONS = 'count_category_suggestions';
    public const SETTING_COUNT_PAGE_SUGGESTIONS = 'count_page_suggestions';
    public const SETTING_LIVESEARCH_POSITION = 'livesearch_position';
    public const SETTING_SEARCHPAGE_ACTIVE = 'search_page_active';
    public const SETTING_SEARCHPAGE_TEMPLATE = 'search_page_template';
    public const SETTING_SHOPPINGCART_ACTIVE = 'shoppingcart_slider_active';
    public const SETTING_SHOPPINGCART_TEMPLATE = 'shopping_cart_template';
    public const SETTING_SHOPPINGCART_SLIDER_POSITION = 'shoppingcart_slider_position';
    public const SETTING_SHOPPINGCART_SLIDER_SELECTOR = 'shoppingcart_slider_selector';
    public const SETTING_SHOPPINGCART_EXCLUDE_DUPLICATES = 'shopping_cart_exclude_duplicates';
    public const SETTING_ARTICLE_SLIDER_ACTIVE = 'article_slider_active';
    public const SETTING_ARTICLE_TEMPLATE = 'article_slider_template';
    public const SETTING_ARTICLE_SLIDER_POSITION = 'article_slider_position';
    public const SETTING_ARTICLE_SLIDER_SELECTOR = 'article_slider_selector';
    public const SETTING_ARTICLE_EXCLUDE_DUPLICATES = 'article_exclude_duplicates';
    public const SETTING_CATEGORY_SLIDER_ACTIVE = 'category_slider_active';
    public const SETTING_CATEGORY_TEMPLATE = 'category_slider_template';
    public const SETTING_CATEGORY_SLIDER_POSITION = 'category_slider_position';
    public const SETTING_CATEGORY_SLIDER_SELECTOR = 'category_slider_selector';
    public const SETTING_CATEGORY_EXCLUDE_DUPLICATES = 'category_exclude_duplicates';
    public const SETTING_EXIT_INTENT_ACTIVE = 'exit_intent_slider_active';
    public const SETTING_EXIT_INTENT_TEMPLATE = 'exit_intent_template';
    public const SETTING_POWERSTEP_ACTIVE = 'powerstep_slider_active';
    public const SETTING_POWERSTEP_POSITION = 'powerstep_position';
    public const SETTING_POWERSTEP_SELECTOR = 'powerstep_selector';
    public const SETTING_POWERSTEP_TEMPLATE = 'powerstep_template';
    public const SETTING_POWERSTEP_EXCLUDE_DUPLICATES = 'powerstep_exclude_duplicates';
    public const SETTING_FACETS_POSITION = 'position_facettes';
    public const SETTING_FACETS_IN_URL = 'facettes_in_url';
    public const SETTING_FACETS_ATTRIBUTES = 'facette_attributes';
    public const SETTING_FACETS_MULTI_ATTRIBUTES = 'facette_multiselect_attributes';
    public const SETTING_CRON_METHOD = 'cron_method';
    public const SETTING_BATCH_SIZE = 'batch_size';
    public const SETTING_CUSTOM_CLERK_JS_NAME = 'custom_clerk_js_name';
    public const SETTING_OMNI_SEARCH_ACTIVE = 'omnisearch_active';
    public const SETTING_OMNI_SEARCH_INSERT_METHOD = 'omnisearch_insert_method';
    public const SETTING_OMNI_SEARCH_SELECTOR = 'omnisearch_selector';
    public const SETTING_OMNI_SEARCH_TEMPLATE = 'omnisearch_template';

    public const TRANS_SEARCH_HEADLINE = 'search_headline';
    public const TRANS_SEARCH_HEADLINE_PRODUCTS = 'search_headline_products';
    public const TRANS_SEARCH_HEADLINE_CATEGORIES = 'search_headline_categories';
    public const TRANS_SEARCH_HEADLINE_SUGGESTIONS = 'search_headline_suggestions';
    public const TRANS_SEARCH_HEADLINE_PAGES = 'search_headline_pages';
    public const TRANS_SEARCH_SHOW_ALL_RESULTS = 'search_show_all_results';
    public const TRANS_SEARCH_LOAD_MORE = 'search_label_load_more';
    public const TRANS_SEARCH_LOAD_MORE_PROGRESS = 'search_label_load_more_progress';
    public const TRANS_NO_RESULTS = 'no_results';
    public const TRANS_FACETS_VIEW_MORE = 'facets_view_more';
    public const TRANS_FACETS_VIEW_ALL = 'facets_view_all';
    public const TRANS_FACETS_SEARCH_FOR = 'facets_search_for';
    public const TRANS_FACETS_HEADLINE_BRAND = 'facets_headline_brand';
    public const TRANS_FACETS_HEADLINE_CATEGORY = 'facets_headline_category';
    public const TRANS_FACETS_HEADLINE_PRICE = 'facets_headline_price';

    public const CRON_JOB_TYPE_GENERATE = 'clerk_generate_feed_cron';
    public const CRON_MODE_TASK = 'task';
    public const CRON_MODE_SYNC = 'sync';
    public const CRON_MODE_CLI = 'cli';

    /**
     * Enables the cronjob as a default JTL task which is run when cron_inc.php is called.
     */
    public function enableCronjobTask(): void
    {
        $job = new stdClass();
        $job->name = 'Clerk Data Feed Generation Cron';
        $job->jobType = self::CRON_JOB_TYPE_GENERATE;
        $job->frequency = 24; // Run every 24 hour
        $job->startDate = 'NOW()';
        $job->startTime = '00:00:00';

        // Clear job from the queue
        Shop::Container()->getDB()->delete('tjobqueue', 'jobType', self::CRON_JOB_TYPE_GENERATE);

        // Update or insert the object
        if (!empty(Shop::Container()->getDB()->select('tcron', 'jobType', $job->jobType))) {
            Shop::Container()->getDB()->update('tcron', 'jobType', $job->jobType, $job);
            return;
        }

        Shop::Container()->getDB()->insert('tcron', $job);
    }

    /**
     * Disables the cronjob as a default JTL task.
     */
    public function disableCronjobTask(): void
    {
        Shop::Container()->getDB()->delete('tcron', 'jobType', self::CRON_JOB_TYPE_GENERATE);
        Shop::Container()->getDB()->delete('tjobqueue', 'jobType', self::CRON_JOB_TYPE_GENERATE);
    }
}
