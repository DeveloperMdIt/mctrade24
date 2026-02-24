<?php declare(strict_types = 1);

/**
 * This page is loaded on return after logging in to Amazon Pay.
 *
 * We need to handle getting the user to the correct language + getting amazon profile information.
 * Most of all, we need to exchange the response code for an access token.
 *
 * Example $_REQUEST:
 * 'code' => string 'RHUHtdNZTziHLBTMZxRh' (length=20)
 * 'scope' => string 'profile payments:widget payments:shipping_address payments:billing_address' (length=74)
 * 'state' => string '{ &#34;location&#34;: &#34;https://jtlshop5.test/Computer-Tablets-Netzwerk&#34;,&#34;csrf&#34;:&#34;&#34;,&#34;lang&#34;:&#34;ger&#34;,&#34;context&#34;:&#34;login&#34; }' (length=170)
 */
use \Plugin\s360_amazonpay_shop5\lib\Controllers\ReturnController;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_RETURN);

$controller = new ReturnController();
$controller->handle();