<?php

declare(strict_types=1);

namespace Plugin\lfs_shopvote\classes;

use Carbon\Carbon;
use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\Validation\Rules\DateTime;
use JTL\Shop;

class LfsShopvote
{
    public const REVIEW_SYNC_DAYS_DEFAULT = 7;
    public const REVIEW_SYNC_DAYS_MIN = 1;
    public const REVIEW_SYNC_DAYS_MAX = 365;

    /**
     * @var PluginInterface
     */
    private $plugin;

    /**
     * @var DbInterface
     */
    private $DB;

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_secret;

    /**
     * @var string
     */
    private $api_url = 'https://api.shopvote.de/';

    /**
     * @var string
     */
    private $auth_token = "";

    /**
     * @var string
     */
    private $auth_token_validity;

    /**
     * @var string|null
     */
    private $shopid = null;

    /**
     * @var string
     */
    private $useragent = 'App.T6Z';

    /**
     * @var string[]
     */
    private $valid_settings = [
        'sv_show_productreview'        => 'pluginsetting',
        'sv_api_key'                   => 'pluginsetting',
        'sv_api_secret'                => 'pluginsetting',
        'sv_graphic_code'              => 'extendedsetting',
        'sv_respect_jtlconsentmanager' => 'pluginsetting'
    ];

    /**
     * @var string
     */
    private $graphicsCode;


    public function __construct(PluginInterface $plugin, DbInterface $db)
    {
        $this->setPlugin($plugin);
        $this->setDB($db);
        $this->setApiKey($this->getPlugin()->getConfig()->getValue('sv_api_key') ?: '');
        $this->setApiSecret($this->getPlugin()->getConfig()->getValue('sv_api_secret') ?: '');

        if (!isset($_SESSION['lfsShopVoteAuthToken'])) {
            $_SESSION['lfsShopVoteAuthToken'] = "";
            $_SESSION['lfsShopVoteAuthTokenValidity'] = 0;
        }

        if (isset($_SESSION['lfsShopVoteAuthToken'])) {
            $this->setAuthToken($_SESSION['lfsShopVoteAuthToken']);
        }

        if (isset($_SESSION['lfsShopVoteShopID'])) {
            $this->setShopid($_SESSION['lfsShopVoteShopID']);
        }
    }

    /**
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @param PluginInterface $plugin
     * @return LfsShopvote
     */
    public function setPlugin(PluginInterface $plugin): LfsShopvote
    {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->api_key;
    }

    /**
     * @param string $api_key
     * @return LfsShopvote
     */
    public function setApiKey(string $api_key): LfsShopvote
    {
        $this->api_key = $api_key;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiSecret(): string
    {
        return $this->api_secret;
    }

    /**
     * @param string $api_secret
     * @return LfsShopvote
     */
    public function setApiSecret(string $api_secret): LfsShopvote
    {
        $this->api_secret = $api_secret;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->api_url;
    }

    /**
     * @param string $api_url
     * @return LfsShopvote
     */
    public function setApiUrl(string $api_url): LfsShopvote
    {
        $this->api_url = $api_url;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->auth_token;
    }

    /**
     * @param string $auth_token
     * @return LfsShopvote
     */
    public function setAuthToken(string $auth_token): LfsShopvote
    {
        $_SESSION['lfsShopVoteAuthToken'] = $auth_token;
        $this->auth_token = $auth_token;
        return $this;
    }

    /**
     * @return int
     */
    public function getAuthTokenValidity(): int
    {
        return $this->auth_token_validity = 0;
    }

    /**
     * @param int $auth_token_validity
     * @return LfsShopvote
     */
    public function setAuthTokenValidity(int $auth_token_validity): LfsShopvote
    {
        $this->auth_token_validity = $auth_token_validity;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShopid(): ?string
    {
        return $this->shopid;
    }

    /**
     * @param string $shopid
     * @return LfsShopvote
     */
    public function setShopid(string $shopid): LfsShopvote
    {
        $_SESSION['lfsShopVoteShopID'] = $shopid;
        $this->shopid = $shopid;
        return $this;
    }

    /**
     * @return string
     */
    public function getUseragent(): string
    {
        return $this->useragent;
    }

    /**
     * @param string $useragent
     * @return LfsShopvote
     */
    public function setUseragent(string $useragent): LfsShopvote
    {
        $this->useragent = $useragent;
        return $this;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->DB;
    }

    /**
     * @param DbInterface $DB
     * @return LfsShopvote
     */
    public function setDB(DbInterface $DB): LfsShopvote
    {
        $this->DB = $DB;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getValidSettings(): array
    {
        return $this->valid_settings;
    }

    /**
     * @param string[] $valid_settings
     * @return LfsShopvote
     */
    public function setValidSettings(array $valid_settings): LfsShopvote
    {
        $this->valid_settings = $valid_settings;
        return $this;
    }

    /**
     * @return string
     */
    public function getGraphicsCode(): string
    {
        return $this->graphicsCode;
    }

    /**
     * @param string $graphicsCode
     * @return LfsShopvote
     */
    public function setGraphicsCode(string $graphicsCode): LfsShopvote
    {
        $this->graphicsCode = $graphicsCode;
        return $this;
    }

    public function renderConfigTab(): string
    {
        Shop::Smarty()->assign('plugin', $this->getPlugin());

        foreach ($this->getValidSettings() as $setting => $type)
        {
            if ($type === 'pluginsetting') {
                $currentValue = $this->getDB()->selectSingleRow('tplugineinstellungen', 'cName', $setting);
                Shop::Smarty()->assign($setting, $currentValue->cWert);
            }
            else {
                $currentValue = $this->getDB()->selectSingleRow('xplugin_lfs_shopvote_config', 'cName', $setting);
                Shop::Smarty()->assign($setting, $currentValue->cWert);
            }
        }

        return Shop::Smarty()->fetch($this->getPlugin()->getPaths()->getAdminPath() . '/template/lfs_shopvote_config.tpl');
    }

    public function saveSettings($post): bool
    {
        $bSuccess = true;

        foreach ($this->getValidSettings() as $setting => $type)
        {
            if ($type === 'pluginsetting') {
                $this->getDB()->executeQueryPrepared(
                    '
                        UPDATE tplugineinstellungen SET cWert = :value
                        WHERE cName = :settingname
                            AND kPlugin = :kPlugin
                    ',
                    [
                        'settingname' => $setting,
                        'value' => $post[$setting],
                        'kPlugin' => $this->getPlugin()->getID()
                    ],
                    ReturnType::AFFECTED_ROWS
                );
            }
            else {
                $this->getDB()->executeQueryPrepared(
                    '
                        UPDATE xplugin_lfs_shopvote_config
                        SET cWert = :value
                        WHERE cName = :settingname
                    ',
                    [
                        'settingname' => $setting,
                        'value' => $post[$setting]
                    ],
                    ReturnType::AFFECTED_ROWS
                );
            }
        }

        return $bSuccess;
    }

    public function returnGraphicsCode():string
    {
        $graphicsCodeObject = $this->getDB()->selectSingleRow('xplugin_lfs_shopvote_config', 'cName', 'sv_graphic_code');

        return $graphicsCodeObject->cWert;
    }

    public function authorize():void
    {
        if (
            $_SESSION['lfsShopVoteAuthTokenValidity'] === 0
            || $_SESSION['lfsShopVoteAuthTokenValidity'] < (time() - 60)
            || $this->getAuthToken() === ""
        ) {
            $headers[] = 'Apikey: ' . $this->getApiKey();
            $headers[] = 'Apisecret: '. $this->getApiSecret();
            $headers[] = 'User-Agent' . $this->getUseragent();

            $token = $this->callShopvoteAPI(
                $this->getApiUrl().'auth',
                $this->getUseragent(),
                $headers
            );

            if ($this->isJson($token)) {
                $this->decodeJwt($token);
                $this->setAuthToken(json_decode($token)->Token);
            }
            else {
                $logservice = Shop::Container()->getLogService();
                $logservice->info($this->getPlugin()->getPluginID() . ": No valid JSON returned");
            }
        }
    }

    public function calculateCombinedAverageReviews($article, $review_data): object
    {
        $oReturn = new \stdClass();
        $oReturn->rating_count   = 0;
        $oReturn->average_rating = 0;
        $tmpTotalRating = 0;

        $oReturn->review_count = count($article->Bewertungen->oBewertung_arr)+$review_data->rating_count;

        if (
            \is_array($article->Bewertungen->oBewertung_arr)
            && count($article->Bewertungen->oBewertung_arr) > 0
        ) {
            foreach ($article->Bewertungen->oBewertung_arr as $review)
            {
                $tmpTotalRating += $review->nSterne;
                $oReturn->rating_count++;
            }
        }

        if (
            \is_array($review_data->reviews)
            && count($review_data->reviews) > 0
        ) {
            foreach ($review_data->reviews as $review)
            {
                $tmpTotalRating += $review->rating_value;
                $oReturn->rating_count++;
            }
        }

        if ($oReturn->review_count > 0) {
            $oReturn->average_rating = $tmpTotalRating/$oReturn->review_count;
        }

        return $oReturn;
    }

    public function getProductReviews(Artikel $article):object
    {
        $productReviewData = new \stdClass();
        $productReviewData->reviews = [];
        $productReviewData->rating_count = 0;
        $productReviewData->rating_value = 0;

        $ids = [
            'aId1' => $article->kArtikel,
        ];

        if ((int)$article->kVaterArtikel === 0)
        {
            $childArticleIds = $this->getDB()->executeQueryPrepared(
                'SELECT kArtikel FROM tartikel WHERE kVaterartikel = :kVaterartikel',
                ['kVaterartikel' => $article->kArtikel],
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (\is_array($childArticleIds) && \count($childArticleIds)>0)
            {
                foreach ($childArticleIds as $childArticle) {
                    $ids['aId' . (count($ids) + 1)] = $childArticle->kArtikel;
                }
            }
        }

        $preparedKeys = array_map(fn($value): string => ':' . $value, array_keys($ids));

        $productReviews = $this->getDB()->executeQueryPrepared(
            'SELECT * FROM `xplugin_lfs_shopvote_reviews` WHERE `article_id` IN (' . implode(', ', $preparedKeys) . ');',
            $ids,
            ReturnType::ARRAY_OF_OBJECTS
        );

        $productReviewData = new \stdClass();
        $productReviewData->reviews = $productReviews;
        $productReviewData->rating_count = 0;
        $productReviewData->rating_value = 0;

        if (count($productReviewData->reviews) > 0) {
            foreach ($productReviewData->reviews as $review) {
                $productReviewData->rating_count += 1;
                $productReviewData->rating_value += $review->rating_value;
            }

            $productReviewData->rating_value = \round(
                $productReviewData->rating_value / $productReviewData->rating_count,
                2
            );
        }

        // maybe limit to 20 reviews

        return $productReviewData;
    }

    public function getIdByArtNr($cArtNr)
    {
        $tmpArticle = $this->getDB()->executeQueryPrepared(
            'SELECT kArtikel FROM tartikel WHERE cArtNr = :cArtNr',
            ['cArtNr' => $cArtNr],
            ReturnType::SINGLE_OBJECT
        );

        if (\is_object($tmpArticle)) {
            return (int) $tmpArticle->kArtikel;
        }

        return 0;
    }

    private function callShopvoteAPI($url, $useragent, $headers, $params = null)
    {
        if ($params !== null) {
            $count = 0;

            foreach ($params as $k => $v) {
                if ($count > 0) {
                    $url .= '&';
                }

                $url .= $k . '=' . $v;

                $count++;
            }
        }

        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        \curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if (\is_array($headers)) {
            \curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $result        = \curl_exec($ch);
        $response_code = \curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ((int)$response_code !== 200) {
            $logservice = Shop::Container()->getLogService();

            $logservice->debug($this->getPlugin()->getPluginID() . ': Got HTTP-Code: '. (int)$response_code. ' from ShopVote-API');

            return null;
        }

        if ($this->isJson($result)) {
            $resultObj = json_decode($result);

            if ((isset($resultObj->Code)) && ((string)$resultObj->Code !== '200'))  {
                $logservice = Shop::Container()->getLogService();

                $logservice->info($this->getPlugin()->getPluginID() . ': Code: '.$resultObj->Code.' received from ShopVote-API - Info: '.$resultObj->Message);

                return null;
            }
            else {
                return $result;
            }
        }

        $logservice = Shop::Container()->getLogService();

        $logservice->info($this->getPlugin()->getPluginID() . ': No valid data received from ShopVote-API');

        return null;
    }

    private function isJson($string): bool
    {
        return \is_string($string)
            && \is_array(json_decode($string, true))
            && (\json_last_error() === JSON_ERROR_NONE);
    }

    private function decodeJwt($token): void
    {
        $decoded = json_decode(
            \base64_decode(
                str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))
            )
        );

        $this->setShopid($decoded->shopid);

        $_SESSION['lfsShopVoteAuthTokenValidity'] = $decoded->exp;
    }

    /**
     * @param int $days
     * @return array
     */
    public function syncNewReviews(int $days = 0)
    {
        if ($days < 1) {
            $days = self::REVIEW_SYNC_DAYS_DEFAULT;
        }

        $days = max(self::REVIEW_SYNC_DAYS_MIN, min(self::REVIEW_SYNC_DAYS_MAX, $days));

        $syncResult = [
            'total'   => 0,
            'success' => 0,
            'errors'  => [],
        ];

        $this->authorize();

        if ($this->getShopid() === null) {
            $syncResult['errors'][] = 'Could not authorize shop, please verify API credentials.';
            
            return $syncResult;
        }

        $headers[] = 'Token: Bearer ' . $this->getAuthToken();
        $headers[] = 'User-Agent: ' . $this->getUseragent() . "." . $this->getShopid();

        $params['sd'] = 'false';
        $params['days'] = $days;

        $apiResult = $this->callShopvoteAPI(
            $this->getApiUrl().'product-reviews/v2/reviews?',
            $this->getUseragent() . '.' . $this->getShopid(),
            $headers,
            $params
        );

        if (empty($apiResult)) {
            $syncResult['errors'][] = 'API response could not be parsed.';
            
            return $syncResult;
        }

        $tmpData = json_decode($apiResult);

        if (!isset($tmpData->reviews)) {
            $syncResult['errors'][] = 'API results did not contain reviews.';
            
            return $syncResult;
        }

        $reviewData = collect(json_decode($apiResult)->reviews);

        $syncResult['total'] = count($reviewData);

        foreach ($reviewData as $row) {
            $articleId = $this->getIdByArtNr($row->sku);

            if (!$articleId) {
                $syncResult['errors'][] = sprintf(
                    '%s: Article number (%s) not found.',
                    $this->getPlugin()->getPluginID(),
                    $row->sku
                );

                continue;
            }

            $insertResult = $this->getDB()->executeQueryPrepared(
                'INSERT IGNORE INTO `xplugin_lfs_shopvote_reviews` (
                    `remote_id`, `sku`, `article_id`, `author_name`, `rating_value`, `text`, `created_at`
                ) VALUES (
                    :remote_id, :sku, :article_id, :author_name, :rating_value, :text, CURDATE()
                );',
                [
                    'remote_id'    => $row->reviewUID,
                    'sku'          => $row->sku,
                    'article_id'   => $articleId,
                    'author_name'  => $row->author,
                    'rating_value' => (float) $row->rating_value,
                    'text'         => $row->text,
                ],
                ReturnType::AFFECTED_ROWS
            );

            if ($insertResult === 0) {
                $syncResult['errors'][] = sprintf(
                    '%s: Article id (%s) was already present (uid: %s), ignored.',
                    $this->getPlugin()->getPluginID(),
                    $articleId,
                    $row->reviewUID
                );
            }
            else {
                $syncResult['success']++;
            }
        }

        return $syncResult;
    }
}