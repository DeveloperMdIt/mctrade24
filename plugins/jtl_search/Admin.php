<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use Exception;
use JTL\DB\DbInterface;
use JTL\Network\Communication;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Admin
 * @package Plugin\jtl_search
 */
class Admin
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var stdClass|null
     */
    private ?stdClass $serverInfo = null;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Admin constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->logger = $logger;
        $this->getServerInfo();
    }

    /**
     * @return stdClass|null
     */
    private function getServerInfo(): ?stdClass
    {
        if ($this->serverInfo !== null) {
            return $this->serverInfo;
        }
        $data = $this->db->getObjects(
            "SELECT cKey, cValue
                FROM tjtlsearchserverdata
                WHERE cKey = 'cServerUrl' OR cKey = 'cAuthHash' OR cKey = 'cProjectId'"
        );
        if (\count($data) > 0) {
            $this->serverInfo = new stdClass();
            foreach ($data as $item) {
                $this->serverInfo->{$item->cKey} = $item->cValue;
            }
        }

        return $this->serverInfo;
    }

    /**
     * @param JTLSmarty       $smarty
     * @param PluginInterface $plugin
     * @return string
     */
    public function getTestPeriodTab(JTLSmarty $smarty, PluginInterface $plugin): string
    {
        $startedTestperiod = false;
        $stepPlugin        = 'testperiod';
        $licenseKey        = '';
        if ($this->getServerInfo() !== null) {
            $licenseKey = \base64_encode($this->serverInfo->cProjectId . ':::' . $this->serverInfo->cAuthHash);
        }

        $form = new Form('JTLSearch_testperiod_form', 'post');
        $form->addElement('kPlugin', 'hidden', '', ['value' => $plugin->getID()]);
        $form->addElement('cPluginTab', 'hidden', '', ['value' => 'Lizenz']);
        $form->addElement('stepPlugin', 'hidden', '', ['value' => $stepPlugin]);
        $form->addElement('cCode', 'textarea', \__('licenceKey'), [
            'style' => 'width: 500px; height: 120px;',
            'value' => $licenseKey,
            'id'    => 'cCode',
            'class' => 'form-control'
        ]);
        $form->addElement(
            'btn_serverinfo',
            'submit',
            '',
            ['value' => \__('save'), 'class' => 'btn btn-primary']
        );

        $form->addRule('cCode', \__('licenceKeyRequired'), 'required');
        $form->addRule('cCode', \__('licenceKeyMinCharCount'), 'minlength', 3);
        $form->addRule('cCode', \__('licenceKeyInvalid'), 'base64decodeable');

        if (isset($_POST['kPlugin'], $_POST['btn_serverinfo']) && $form->isValid()) {
            $data = \explode(':::', \base64_decode($_POST['cCode']));

            // Security Objekt erstellen und Parameter zum senden der Daten setzen
            $security = new Security($data[0], $data[1]);
            $security->setParams(['getsearchserver']);

            $request['a']   = 'getsearchserver';
            $request['pid'] = $data[0];
            $request['p']   = $security->createKey();
            $postData       = Communication::postData(\JTLSEARCH_MANAGER_SERVER_URL, $request);
            $result         = \json_decode($postData);

            if (
                isset($result->_serverurl, $result->_code)
                && $result->_code === 1
                && \strlen($result->_serverurl) > 0
            ) {
                $startedTestperiod = true;
                try {
                    $this->db->query('TRUNCATE TABLE tjtlsearchserverdata');
                    $ins         = new stdClass();
                    $ins->cKey   = 'cProjectId';
                    $ins->cValue = $data[0];
                    $this->db->insert('tjtlsearchserverdata', $ins);
                    $ins         = new stdClass();
                    $ins->cKey   = 'cAuthHash';
                    $ins->cValue = $data[1];
                    $this->db->insert('tjtlsearchserverdata', $ins);
                    $ins         = new stdClass();
                    $ins->cKey   = 'cServerUrl';
                    $ins->cValue = $result->_serverurl;
                    $this->db->insert('tjtlsearchserverdata', $ins);
                    unset($ins);
                    $this->db->query('TRUNCATE TABLE tjtlsearchexportlanguage');
                    $this->db->query(
                        'INSERT INTO tjtlsearchexportlanguage (`cISO`) 
                            SELECT tsprache.cISO FROM tsprache GROUP BY tsprache.cISO'
                    );
                    Shop::Container()->getCache()->flushTags([$plugin->getCache()->getGroup()]);

                    // Boxenverwaltung aktualisieren
                    Filterbox::create($this->db);
                } catch (Exception $e) {
                    $this->logger->warning(\sprintf(\__('loggerLicenceUnknownError'), __CLASS__));
                    $this->logger->warning(__CLASS__ . ': Exception $oEx : ' . $e);
                }
            } else {
                if (\strlen($postData) > 0) {
                    $form->setError(\__('licenceKeyProblem'));
                } else {
                    $form->setError(\__('licenceKeyUnknownServerProblem'));
                }
                $this->logger->warning(\sprintf(\__('loggerExportUnknownError'), __CLASS__));
                $this->logger->warning(__CLASS__ . ': result: ' . \print_r($result, true));
            }
        }

        return $smarty->assign('startedTestperiod', $startedTestperiod)
            ->assign('baseCssURL', $plugin->getPaths()->getAdminURL() . 'css/testperiod.css')
            ->assign('form', $form)
            ->fetch(\JTLSEARCH_ADMIN_TPL_PATH . 'testperiod.tpl');
    }

    /**
     * @param JTLSmarty       $smarty
     * @param PluginInterface $plugin
     * @return string
     */
    public function getManageTab(JTLSmarty $smarty, PluginInterface $plugin): string
    {
        if ($this->getServerInfo() === null) {
            return $this->getTestPeriodTab($smarty, $plugin);
        }
        $pluginStep = 'Verwaltung';
        $modules    = [];
        foreach ([ManageExport::class, ManageStatus::class] as $className) {
            $status  = new $className($this->logger, $this->db, $this->serverInfo);
            $content = $status->getContent();
            if ($content !== null) {
                $module['name']   = $status->getName();
                $module['cssURL'] = $status->getCssURL();
                foreach ($content['xContentVarAssoc'] as $key => $value) {
                    $smarty->assign($key, $value);
                }
                $module['content'] = $smarty->fetch($content['cTemplate']);
                if (isset($modules[$status->getSort()])) {
                    $modules[] = $module;
                } else {
                    $modules[$status->getSort()] = $module;
                }
            }
        }
        \ksort($modules);

        return $smarty->assign('stepPlugin', $pluginStep)
            ->assign('baseCssURL', $plugin->getPaths()->getAdminURL() . 'css/manage.css')
            ->assign('modules', $modules)
            ->fetch(\JTLSEARCH_ADMIN_TPL_PATH . 'manage.tpl');
    }
}
