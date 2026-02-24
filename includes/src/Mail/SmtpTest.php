<?php

declare(strict_types=1);

namespace JTL\Mail;

use JTL\Settings\Option\Email;
use JTL\Settings\Settings;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\SMTP;

/**
 * Class SmtpTest
 * @package JTL\Mail
 */
class SmtpTest
{
    protected function runGeneric(
        string $host,
        int $port,
        string $smtpEncryption,
        string $user,
        string $pass,
        ?string $authType = null,
        ?OAuth $oauth = null,
    ): bool {
        $smtp = new SMTP();
        $smtp->setDebugLevel(SMTP::DEBUG_CONNECTION);
        try {
            if (!$smtp->connect($host, $port)) {
                throw new Exception('Connect failed');
            }
            if (!$smtp->hello(\gethostname())) {
                throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
            }
            $e = $smtp->getServerExtList();
            if (\is_array($e) && \array_key_exists('STARTTLS', $e)) {
                $tlsok = $smtp->startTLS();
                if (!$tlsok) {
                    throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
                }
                if (!$smtp->hello(\gethostname())) {
                    throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
                }
                $e = $smtp->getServerExtList();
            } elseif ($smtpEncryption === 'tls') {
                throw new Exception('TLS not supported');
            }
            if (!\is_array($e) || !\array_key_exists('AUTH', $e)) {
                throw new Exception('No authentication supported');
            }
            try {
                $result = $smtp->authenticate($user, $pass, $authType, $oauth);
            } catch (\Exception $e) {
                throw new Exception('Authentication failed: ' . $e->getMessage());
            }
            if (!$result) {
                throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
            }
            echo 'Connected ok!';
        } catch (Exception $e) {
            echo 'SMTP error: ' . $e->getMessage(), "\n";
        }

        return $smtp->quit();
    }

    public function run(Settings $settings): bool
    {
        return $this->runGeneric(
            host:           $settings->string(Email::SMTP_HOST),
            port:           $settings->int(Email::SMTP_PORT),
            smtpEncryption: $settings->string(Email::SMTP_ENCRYPTION),
            user:           $settings->string(Email::SMTP_USER),
            pass:           $settings->string(Email::SMTP_PASS),
        );
    }
}
