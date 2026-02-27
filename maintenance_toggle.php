<?php
/**
 * JTL Maintenance Toggle Bridge (Secure)
 * This script is used by GitHub Actions to toggle maintenance mode during deployment.
 */

require_once 'includes/config.JTL-Shop.ini.php';

// Simple Security Token Check
$actionToken = 'mctrade_deploy_token_2026';
$providedToken = $_GET['token'] ?? '';

if ($providedToken !== $actionToken) {
    http_response_code(403);
    die('Unauthorized');
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    $mode = $_GET['mode'] === 'on' ? 'Y' : 'N';

    $stmt = $pdo->prepare("UPDATE teinstellungen SET cWert = ? WHERE cName = 'global_wartungsmodus'");
    $stmt->execute([$mode]);

    echo "Maintenance mode set to: " . $mode;
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
