<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Network\Communication;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class ManageStatus
 * @package Plugin\jtl_search
 */
class ManageStatus extends ManageBase
{
    /**
     * @inheritdoc
     */
    public function __construct(LoggerInterface $logger, DbInterface $db, ?stdClass $serverInfo)
    {
        $this->logger          = $logger;
        $this->db              = $db;
        $this->serverInfo      = $serverInfo;
        $this->contentTemplate = \JTLSEARCH_ADMIN_TPL_PATH . 'manage_status.tpl';
    }

    /**
     * @inheritdoc
     */
    public function generateContent(bool $force = false): void
    {
        if ($force === true || $this->getIssetContent() === false) {
            $this->setIssetContent(true)
                ->setSort(1)
                ->setName(\__('Index status'))
                ->setContentVar('indexStatus', $this->getIndexStatus());
            $this->getServereinstellungenURL();
        }
    }

    /**
     * @return array|mixed
     */
    public function getIndexStatus()
    {
        $security = new Security($this->serverInfo->cProjectId, $this->serverInfo->cAuthHash);
        $security->setParams(['getindexstatus']);

        $data['a']   = 'getindexstatus';
        $data['p']   = $security->createKey();
        $data['pid'] = $this->serverInfo->cProjectId;

        $postData = Communication::postData(
            \urldecode($this->serverInfo->cServerUrl) . 'importdaemon/index.php',
            $data
        );

        if (\strlen($postData) > 0) {
            $indexStatus = \json_decode($postData);
            if ($indexStatus === null || $indexStatus === false) {
                $indexStatus = [];
            }

            return $indexStatus;
        }

        return [];
    }

    /**
     *
     */
    protected function getServereinstellungenURL(): void
    {
        $security = new Security($this->serverInfo->cProjectId, $this->serverInfo->cAuthHash);
        $security->setParams([Shop::getURL()]);

        $serverConfigURL = false;
        if (Request::verifyGPDataString('a') === 'createtmplogin') {
            $requestURL = \str_replace(
                'https',
                'http',
                \urldecode($this->serverInfo->cServerUrl)
            );

            $requestURL .= 'admin/adminlogin/index/pid/'
                . $this->serverInfo->cProjectId
                . '/auth/' . $security->createKey();
            // JTL Search loginId request
            $loginID = $this->requestJTLSearchLoginId($requestURL);

            if ($loginID !== null) {
                $serverConfigURL = \str_replace(
                    'https',
                    'http',
                    \urldecode($this->serverInfo->cServerUrl)
                );

                $serverConfigURL .= 'admin/index/login/pid/' . $this->serverInfo->cProjectId . '/auth/' . $loginID;
            }
        }
        $this->setContentVar('cServereinstellungenURL', $serverConfigURL);
    }

    /**
     * @param string $requestURL
     * @return mixed|null|string
     */
    protected function requestJTLSearchLoginId(string $requestURL)
    {
        if (!\function_exists('curl_init') || \strlen($requestURL) === 0) {
            return null;
        }
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_POST, true);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, []);
        \curl_setopt(
            $ch,
            \CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1'
        );
        \curl_setopt($ch, \CURLOPT_URL, $requestURL);
        \curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, \CURLOPT_ENCODING, 'UTF-8');
        \curl_setopt($ch, \CURLOPT_AUTOREFERER, true);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 60);
        \curl_setopt($ch, \CURLOPT_TIMEOUT, 60);
        \curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, true);
        \curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, 2);

        $content = \curl_exec($ch);

        \curl_close($ch);

        $content = \trim($content);
        if ($content !== '0') {
            return $content;
        }

        return null;
    }
}
