<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Controllers;

use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Models\StoreModel;
use Plugin\s360_clerk_shop5\src\Utils\Helpers;
use Plugin\s360_clerk_shop5\src\Utils\LoggerTrait;

final class FeedController extends Controller
{

    use LoggerTrait;

    public function handle()
    {
        // Get feed from hash
        $hash = $_GET['id'] ?? ltrim($_SERVER['PATH_INFO'] ?? '', '/');
        $hash = basename((string) $hash);
        $timestamp = time();

        $helper = new Helpers($this->plugin);
        $model  = new StoreModel();
        $feed   = $model->getByHash($hash);

        // TODO: Setting Generate on the fly
        // $generator = new FeedGenerator();
        // $generator->createFeed($feed);
        // Feed does not exist -> abort
        if (empty($feed)) {
            http_response_code(404);
            return;
        }

        $filename = $helper->getFeedFilePath($feed);
        if (!file_exists($filename)) {
            http_response_code(404);
            return;
        }

        $verifySucceeded = false;

        if (!empty($_SERVER['HTTP_X_CLERK_AUTHORIZATION'])) {
            $token = str_replace('Bearer ', '', (string) $_SERVER['HTTP_X_CLERK_AUTHORIZATION']);

            $url = $helper->getClerkVerifyApiUrl() . '?token=' . $token . '&key=' . $feed->getApiKey();

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_TIMEOUT, 3);

            $result = curl_exec($curl);

            if ($result !== false) {
                $data = json_decode($result);

                if ($data->status === 'ok') {
                    $verifySucceeded = true;
                }
            }
            else {
                $this->errorLog(
                    'Could not authenticate token: ' . print_r(
                        ['timestamp' => $timestamp, 'curl error' => curl_error($curl)],
                        true
                    ), __METHOD__
                );
            }
            curl_close($curl);
        }
        elseif (array_key_exists('salt', $_GET) && $_GET['salt'] && array_key_exists('hash', $_GET) && $_GET['hash']) {
            $verifySucceeded = false;
            $salt            = $_GET['salt'] ?? '';
            $hash            = $_GET['hash'] ?? '';
            $correctHash     = hash(
                'sha512',
                ($salt . $feed->getPrivateKey() . (string) ((int) floor($timestamp / 100)))
            );

            if ($correctHash === $hash) {
                $verifySucceeded = true;
            }
        }

        if (!$feed->getSettings()?->getDisableAuthCheck() && !$verifySucceeded && !Shop::isAdmin()) {
            // No Hash and not salt were in the request -> abort
            if ($salt === '' && $hash === '') {
                $this->noticeLog(
                    'Could not authenticate. Request is missing salt and hash: ' . print_r(
                        ['timestamp' => $timestamp, 'request' => $_GET], true
                    ), __METHOD__
                );
            }

            if (($_SERVER["REQUEST_METHOD"] ?? false) !== "OPTIONS") {
                $this->errorLog(
                    'Could not authenticate: ' . print_r(
                        ['timestamp' => $timestamp, 'request' => $_GET, 'hash' => $correctHash, "server" => $_SERVER],
                        true
                    ), __METHOD__
                );
            }
            http_response_code(403);
            return;
        }

        // Everything's alright -> Show Feed
        header('Content-Type: text/json; charset=utf-8');
        $handle = fopen($filename, 'r');
        if ($handle) {
            while (!feof($handle)) {
                echo fgets($handle, 4096);
            }
            fclose($handle);
        }
    }
}
