<?php

namespace Plugin\jtl_search\ExportModules;

use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class JTLSearchExportQueue
 * @package Plugin\jtl_search\ExportModules
 */
class JTLSearchExportQueue
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $counts = ['category' => 0, 'manufacturer' => 0, 'product' => 0];

    /**
     * @var array
     */
    private $xExportObject_arr;

    /**
     * @var array
     */
    private $membersToSave = [
        'kExportqueue',
        'nLimitN',
        'nLimitM',
        'nExportMethod',
        'bFinished',
        'bLocked',
        'dStartTime',
        'dLastRun'
    ];

    /**
     * @var int
     */
    protected $kExportqueue;

    /**
     * @var int
     */
    protected $nLimitN;

    /**
     * @var int
     */
    protected $nLimitM;

    /**
     * @var int
     */
    protected $nExportMethod;

    /**
     * @var bool
     */
    protected $bFinished;

    /**
     * @var bool
     */
    protected $bLocked;

    /**
     * @var string
     */
    protected $dStartTime;

    /**
     * @var string
     */
    protected $dLastRun;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * JTLSearchExportQueue constructor.
     * @param LoggerInterface $logger
     * @param DbInterface     $db
     * @param int             $nExportMethod
     */
    public function __construct(LoggerInterface $logger, DbInterface $db, int $nExportMethod)
    {
        $this->logger = $logger;
        $this->db     = $db;
        if (\is_numeric($nExportMethod) && $nExportMethod > 0) {
            $this->loadFromDB($nExportMethod);
        } else {
            die(
                'JTL-Search Fehler beim Datenexport: '
                . 'nExportMethod hat einen invaliden Wert. Es wird ein ganzzahliger Wert >= 1 erwartet.'
            );
        }
    }

    /**
     * Loads database member into class member
     * @param int $exportMethod
     * @return $this
     */
    private function loadFromDB(int $exportMethod): self
    {
        $data = $this->db->getSingleObject(
            'SELECT *
                 FROM tjtlsearchexportqueue
                 WHERE nExportMethod = :eid 
                 AND bFinished = 0 
                 AND bLocked = 0',
            ['eid' => $exportMethod]
        );

        if ($data !== null && $data->kExportqueue > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
            }
            $this->bLocked = 1;
            $this->update();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isExportFinished(): bool
    {
        return $this->getSumCount() <= $this->nLimitN;
    }

    /**
     * @param string $key
     * @param int    $value
     * @return $this
     */
    public function setCount($key, $value): self
    {
        if (\is_string($key) && isset($this->counts[$key]) && \is_int($value)) {
            $this->counts[$key] = $value;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSumCount()
    {
        $res = 0;
        foreach ($this->counts as $nCount) {
            if (\is_int($nCount)) {
                $res += $nCount;
            }
        }

        return $res;
    }

    /**
     * @return $this
     */
    private function loadExportObjects(): self
    {
        if ((int)$this->nExportMethod === 3) {
            $exports = $this->db->getObjects(
                'SELECT kId, eDocumentType 
                    FROM tjtlsearchdeltaexport
                    WHERE bDelete = 0 
                    LIMIT 0, ' . \JTLSEARCH_LIMIT_N_METHOD_3
            );
            foreach ($exports as $export) {
                $this->xExportObject_arr[] = [$export->eDocumentType, $export->kId];
            }
        } else {
            $tmp  = $this->nLimitN;
            $left = $this->nLimitM;
            $prev = 0;
            foreach ($this->counts as $key => $count) {
                if ($this->nLimitN > ($prev + $count)) { // von diesem Typ wurden schon alle Items exportiert
                    $exported = $count;
                    $run      = 0;
                    $prev    += $count;
                } else { //von diesem Typ sind noch Items zum Exportieren übrig
                    if ($count < $left) { //alle verbleibenden Items von diesem Typ in die Queue packen
                        $exported = $tmp - $prev;
                        $run      = $count - $exported;

                        $left -= $run;
                    } else { //ein Teil der Items in die Queue packen
                        $exported = $tmp - $prev;
                        //Anzahl verbleibender Items für diesen Typ und Lauf berechnen
                        if ($count - $exported < $left) { // Anzahl verbleibender Items ist kleiner als nLeftover
                            $run = $count - $exported;
                        } else { //Sonst
                            $run = $left;
                        }
                        $left -= $run;
                    }
                    $prev += $run + $exported;
                    $tmp  += $run;
                }
                if ($run > 0) {
                    $className = 'Plugin\jtl_search\ExportModules\\' . $key . 'Data';
                    foreach (\call_user_func([$className, 'getItemKeys'], $this->db, $exported, $run) as $res) {
                        $this->xExportObject_arr[] = [$key, (int)$res->kItem];
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return object|null
     */
    public function getNextExportObject()
    {
        $result = null;
        if ($this->xExportObject_arr === null) {
            $this->loadExportObjects();
        }
        if (\is_array($this->xExportObject_arr) && \count($this->xExportObject_arr) > 0) {
            foreach ($this->xExportObject_arr as $key => $exportObject) {
                $result = $exportObject;
                unset($this->xExportObject_arr[$key]);
                $this->nLimitN++;
                break;
            }
        } else {
            return null;
        }

        return $result;
    }

    /**
     * @param int         $exportMethod
     * @param DbInterface $db
     *
     * @return bool
     * @throws \Exception
     */
    public static function generateNew(int $exportMethod, DbInterface $db): bool
    {
        $queue = $db->getSingleObject(
            'SELECT COUNT(*) AS nCount 
                FROM tjtlsearchexportqueue 
                WHERE nExportMethod = :mid 
                AND bFinished = 0',
            ['mid' => $exportMethod]
        );
        if (isset($queue) && $queue->nCount > 0) {
            return false;
        }

        $data          = new stdClass();
        $data->nLimitN = 0;
        switch ($exportMethod) {
            case 3:
                $data->nLimitM = \JTLSEARCH_LIMIT_N_METHOD_3;
                break;

            case 2:
                $data->nLimitM = \JTLSEARCH_LIMIT_N_METHOD_2;
                break;

            case 1:
            default:
                $data->nLimitM = \JTLSEARCH_LIMIT_N_METHOD_1;
                break;
        }
        $data->bFinished     = 0;
        $data->bLocked       = 0;
        $data->dStartTime    = \date('Y-m-d H:i:s');
        $data->dLastRun      = '0000-00-00 00:00:00';
        $data->nExportMethod = $exportMethod;
        if ($db->insert('tjtlsearchexportqueue', $data) > 0) {
            return true;
        }

        throw new \Exception('An error occured while writing the new Export into the database.');
    }

    /**
     * Store the class in the database
     * @return $this
     */
    public function save(): self
    {
        if (isset($this->kExportqueue) && $this->kExportqueue > 0) {
            $this->bLocked = 0;
            $this->update();
        } else {
            $object = new stdClass();
            if (\is_array($this->membersToSave) && count($this->membersToSave) > 0) {
                foreach ($this->membersToSave as $member) {
                    $object->$member = $this->$member;
                }
            }
            unset($object->kExportqueue);
            $this->kExportqueue = $this->db->insert('tjtlsearchexportqueue', $object);
        }

        return $this;
    }

    /**
     * @return int
     */
    private function update(): int
    {
        $upd                = new stdClass();
        $upd->nLimitN       = $this->nLimitN;
        $upd->nLimitM       = $this->nLimitM;
        $upd->nExportMethod = $this->nExportMethod;
        $upd->bFinished     = $this->bFinished;
        $upd->dStartTime    = $this->dStartTime;
        $upd->dLastRun      = $this->dLastRun;

        return $this->db->update('tjtlsearchexportqueue', 'kExportqueue', $this->kExportqueue, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return $this->db->deleteRow('tjtlsearchexportqueue', 'kExportqueue', $this->kExportqueue);
    }

    /**
     * @param int $kExportqueue
     * @return $this
     */
    public function setExportqueue(int $kExportqueue): self
    {
        $this->kExportqueue = $kExportqueue;

        return $this;
    }

    /**
     * @param int $nLimitN
     * @return $this
     */
    public function setLimitN(int $nLimitN): self
    {
        $this->nLimitN = $nLimitN;

        return $this;
    }

    /**
     * @param int $nLimitM
     * @return $this
     */
    public function setLimitM(int $nLimitM): self
    {
        $this->nLimitM = $nLimitM;

        return $this;
    }

    /**
     * @param int $exportMethod
     * @return $this
     */
    public function setExportMethod(int $exportMethod): self
    {
        $this->nExportMethod = $exportMethod;

        return $this;
    }

    /**
     * @param int $bFinished
     * @return $this
     */
    public function setFinished($bFinished): self
    {
        $this->bFinished = $bFinished;

        return $this;
    }

    /**
     * @param string $dStartTime
     * @return $this
     */
    public function setStartTime($dStartTime): self
    {
        $this->dStartTime = $dStartTime;

        return $this;
    }

    /**
     * @param string $dLastRun
     * @return $this
     */
    public function setLastRun($dLastRun): self
    {
        $this->dLastRun = $dLastRun;

        return $this;
    }

    /**
     * @return int
     */
    public function getExportqueue()
    {
        return $this->kExportqueue;
    }

    /**
     * @return int
     */
    public function getLimitN()
    {
        return $this->nLimitN;
    }

    /**
     * @return int
     */
    public function getLimitM()
    {
        return $this->nLimitM;
    }

    /**
     * @return int
     */
    public function getExportMethod()
    {
        return (int)$this->nExportMethod;
    }

    /**
     * @return bool
     */
    public function getFinished()
    {
        return $this->bFinished;
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->dStartTime;
    }

    /**
     * @return string
     */
    public function getLastRun()
    {
        return $this->dLastRun;
    }

    /**
     * @param bool        $all
     * @param null|string $fullPath
     * @return array|string
     */
    public function getFileName(bool $all = false, $fullPath = null)
    {
        if (\is_string($fullPath) && \strlen($fullPath) > 0) {
            $path = $fullPath . 'tmpSearchExport' . $this->nExportMethod . '/';
        } else {
            $path = '';
        }
        $fileNo = (int)($this->nLimitN / \JTLSEARCH_FILE_LIMIT);
        if ($all === true) {
            $res = [];
            for ($i = 0; $i <= $fileNo; $i++) {
                $res[] = $path . \JTLSEARCH_FILE_NAME . $i . \JTLSEARCH_FILE_NAME_SUFFIX;
            }

            return $res;
        }

        return $path . \JTLSEARCH_FILE_NAME . $fileNo . \JTLSEARCH_FILE_NAME_SUFFIX;
    }
}
