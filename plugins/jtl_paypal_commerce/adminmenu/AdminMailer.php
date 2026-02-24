<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu;

use Exception;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\TemplateFactory;
use JTL\Shop;
use JTL\Smarty\MailSmarty;
use Plugin\jtl_paypal_commerce\Repositories\AdminMailerRepository;

/**
 * Class Mailer
 * @package Plugin\jtl_paypal_commerce\adminmenu
 */
class AdminMailer
{
    private LanguageModel $language;

    private string $emailMaster;

    private RendererInterface $renderer;

    private TemplateFactory $tplFactory;

    private AdminMailerRepository $repository;

    /**
     * Mailer constructor
     */
    public function __construct(
        ?DbInterface $db = null,
        ?LanguageModel $language = null,
        ?AdminMailerRepository $repository = null
    ) {
        $configMailSection = Shop::getSettingSection(\CONF_EMAILS);
        $db                = $db ?? Shop::Container()->getDB();
        $this->renderer    = new SmartyRenderer(new MailSmarty($db));
        $this->tplFactory  = new TemplateFactory($db);
        $this->repository  = $repository ?? new AdminMailerRepository($db);
        $this->emailMaster = ($configMailSection['email_master_absender_name'] ?? '')
            . ' <' . ($configMailSection['email_master_absender'] ?? '') . '>';

        try {
            $this->language = $language ?? LanguageModel::loadByAttributes(
                ['iso' => Shop::getCurAdminLangTag() === 'de-DE' ? 'ger' : 'eng'],
                $db,
            );
        } catch (Exception) {
            $this->language = LanguageHelper::getDefaultLanguage();
        }
    }

    private function getMailText(string $mailTplId): string
    {
        $mailTpl = $this->tplFactory->getTemplate($mailTplId);
        if ($mailTpl === null) {
            return '';
        }

        $mailTpl->render($this->renderer, $this->language->getId(), CustomerGroup::getDefaultGroupID());

        return $mailTpl->getText() ?? '';
    }

    private function getMailHeader(): string
    {
        return $this->getMailText(\MAILTEMPLATE_HEADER);
    }

    private function getMailFooter(): string
    {
        return $this->getMailText(\MAILTEMPLATE_FOOTER);
    }

    /**
     * @return string[]
     */
    private function getAdminRecipients(): array
    {
        $recipients = [];

        foreach ($this->repository->getAdminList() as $adminAccount) {
            $recipients[] = $adminAccount->cName . ' <' . $adminAccount->cMail . '>';
        }

        if (empty($recipients)) {
            $recipients[] = $this->emailMaster;
        }

        return $recipients;
    }

    /**
     * @param string $mailName
     * @return string[]
     */
    public function splitMailName(string $mailName): array
    {
        $return = [];
        if (\preg_match('/<([\w\-.]+@[\w\-.]+)>/', $mailName, $hits)) {
            $return[0] = \trim($hits[1]);
            $name      = \trim(\str_replace($hits[0], '', $mailName));
            if ($mailName !== '') {
                $return[1] = $name;
            }
        } else {
            $return[0] = $mailName;
        }

        return $return;
    }

    public function prepare(string $to, string $subject, string $content, bool $debug = false): Mail
    {
        $mail     = new Mail();
        $sendTo   = $this->splitMailName($to);
        $sendFrom = $this->splitMailName($this->emailMaster);

        $mail->setToMail($sendTo[0])
             ->setToName($sendTo[1] ?? $sendTo[0])
             ->setFromMail($sendFrom[0])
             ->setFromName($sendFrom[1] ?? $sendFrom[0])
             ->setReplyToMail($sendFrom[0])
             ->setReplyToName($sendFrom[1] ?? $sendFrom[0])
             ->setSubject($subject)
             ->setBodyText($this->getMailHeader() . $content . $this->getMailFooter())
             ->setLanguage($this->language)
             ->setPriority(0);

        if ($debug) {
            $mail->setCopyRecipients([$sendFrom[0]]);
        }

        return $mail;
    }

    public function prepareForAdmin(string $subject, string $content, bool $debug = false): Mail
    {
        $sendTo = $this->getAdminRecipients();
        $mail   = $this->prepare(\array_shift($sendTo), $subject, $content, $debug);
        if (count($sendTo) > 0) {
            foreach ($sendTo as $recipient) {
                $to = $this->splitMailName($recipient);
                $mail->addRecipient($to[0], $to[1] ?? $to[0]);
            }
        }

        return $mail;
    }
}
