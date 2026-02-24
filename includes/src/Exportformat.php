<?php

/** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace JTL;

use Exception;
use JTL\Cron\QueueEntry;
use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;
use Psr\Log\LoggerInterface;
use SmartyException;
use stdClass;

/**
 * Class Exportformat
 * @package JTL
 * @deprecated since 5.1.0
 */
class Exportformat
{
    public const SYNTAX_FAIL        = 1;
    public const SYNTAX_NOT_CHECKED = -1;
    public const SYNTAX_OK          = 0;

    protected int $kExportformat;

    protected int $kKundengruppe = 0;

    protected int $kSprache = 0;

    protected int $kWaehrung = 0;

    protected int $kKampagne = 0;

    protected int $kPlugin = 0;

    protected string $cName = '';

    protected string $cDateiname = '';

    protected string $cKopfzeile = '';

    protected string $cContent = '';

    protected string $cFusszeile = '';

    protected string $cKodierung = '';

    protected int $nSpecial = 0;

    protected int $nVarKombiOption = 0;

    protected int $nSplitgroesse = 0;

    protected ?string $dZuletztErstellt = null;

    protected int $nUseCache = 1;

    protected ?JTLSmarty $smarty = null;

    protected array $config = [];

    protected ?QueueEntry $queue = null;

    protected ?object $currency = null;

    private bool $isOk = false;

    private ?string $tempFileName = null;

    private ?string $tempFile = null;

    private ?LoggerInterface $logger = null;

    private ?DbInterface $db;

    protected int $nFehlerhaft = 0;

    public function __construct(int $id = 0, ?DbInterface $db = null)
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        $this->db            = $db ?? Shop::Container()->getDB();
        $this->kExportformat = $id;
    }

    public function getDB(): DbInterface
    {
        return $this->db;
    }

    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function isOK(): bool
    {
        return $this->isOk;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true): bool|int
    {
        $ins                   = new stdClass();
        $ins->kKundengruppe    = $this->kKundengruppe;
        $ins->kSprache         = $this->kSprache;
        $ins->kWaehrung        = $this->kWaehrung;
        $ins->kKampagne        = $this->kKampagne;
        $ins->kPlugin          = $this->kPlugin;
        $ins->cName            = $this->cName;
        $ins->cDateiname       = $this->cDateiname;
        $ins->cKopfzeile       = $this->cKopfzeile;
        $ins->cContent         = $this->cContent;
        $ins->cFusszeile       = $this->cFusszeile;
        $ins->cKodierung       = $this->cKodierung;
        $ins->nSpecial         = $this->nSpecial;
        $ins->nVarKombiOption  = $this->nVarKombiOption;
        $ins->nSplitgroesse    = $this->nSplitgroesse;
        $ins->dZuletztErstellt = empty($this->dZuletztErstellt) ? '_DBNULL_' : $this->dZuletztErstellt;
        $ins->nUseCache        = $this->nUseCache;
        $ins->nFehlerhaft      = self::SYNTAX_NOT_CHECKED;

        $this->kExportformat = $this->db->insert('texportformat', $ins);
        if ($this->kExportformat > 0) {
            return $bPrim ? $this->kExportformat : true;
        }

        return false;
    }

    public function update(): int
    {
        $upd                   = new stdClass();
        $upd->kKundengruppe    = $this->kKundengruppe;
        $upd->kSprache         = $this->kSprache;
        $upd->kWaehrung        = $this->kWaehrung;
        $upd->kKampagne        = $this->kKampagne;
        $upd->kPlugin          = $this->kPlugin;
        $upd->cName            = $this->cName;
        $upd->cDateiname       = $this->cDateiname;
        $upd->cKopfzeile       = $this->cKopfzeile;
        $upd->cContent         = $this->cContent;
        $upd->cFusszeile       = $this->cFusszeile;
        $upd->cKodierung       = $this->cKodierung;
        $upd->nSpecial         = $this->nSpecial;
        $upd->nVarKombiOption  = $this->nVarKombiOption;
        $upd->nSplitgroesse    = $this->nSplitgroesse;
        $upd->dZuletztErstellt = empty($this->dZuletztErstellt) ? '_DBNULL_' : $this->dZuletztErstellt;
        $upd->nUseCache        = $this->nUseCache;
        $upd->nFehlerhaft      = self::SYNTAX_NOT_CHECKED;

        return $this->db->update('texportformat', 'kExportformat', $this->getExportformat(), $upd);
    }

    public function setTempFileName(string $name): self
    {
        $this->tempFileName = \basename($name);
        $this->tempFile     = \PFAD_ROOT . \PFAD_EXPORT . $this->tempFileName;

        return $this;
    }

    public function delete(): int
    {
        return $this->db->delete('texportformat', 'kExportformat', $this->getExportformat());
    }

    public function setExportformat(int $kExportformat): self
    {
        $this->kExportformat = $kExportformat;

        return $this;
    }

    public function setKundengruppe(int $customerGroupID): self
    {
        $this->kKundengruppe = $customerGroupID;

        return $this;
    }

    public function setSprache(int $languageID): self
    {
        $this->kSprache = $languageID;

        return $this;
    }

    public function setWaehrung(int $kWaehrung): self
    {
        $this->kWaehrung = $kWaehrung;

        return $this;
    }

    public function setKampagne(int $kKampagne): self
    {
        $this->kKampagne = $kKampagne;

        return $this;
    }

    public function setPlugin(int $kPlugin): self
    {
        $this->kPlugin = $kPlugin;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->cName = $name;

        return $this;
    }

    public function setDateiname(string $cDateiname): self
    {
        $this->cDateiname = $cDateiname;

        return $this;
    }

    public function setKopfzeile($cKopfzeile): self
    {
        $this->cKopfzeile = $cKopfzeile;

        return $this;
    }

    public function setContent(string $cContent): self
    {
        $this->cContent = $cContent;

        return $this;
    }

    public function setFusszeile(string $cFusszeile): self
    {
        $this->cFusszeile = $cFusszeile;

        return $this;
    }

    public function setKodierung(string $cKodierung): self
    {
        $this->cKodierung = $cKodierung;

        return $this;
    }

    public function setSpecial(int $nSpecial): self
    {
        $this->nSpecial = $nSpecial;

        return $this;
    }

    public function setVarKombiOption(int $nVarKombiOption): self
    {
        $this->nVarKombiOption = $nVarKombiOption;

        return $this;
    }

    public function setSplitgroesse(int $nSplitgroesse): self
    {
        $this->nSplitgroesse = $nSplitgroesse;

        return $this;
    }

    public function setZuletztErstellt(string $dZuletztErstellt): self
    {
        $this->dZuletztErstellt = $dZuletztErstellt;

        return $this;
    }

    public function getExportformat(): int
    {
        return $this->kExportformat;
    }

    public function getKundengruppe(): int
    {
        return $this->kKundengruppe;
    }

    public function getSprache(): int
    {
        return $this->kSprache;
    }

    public function getWaehrung(): int
    {
        return $this->kWaehrung;
    }

    public function getKampagne(): int
    {
        return $this->kKampagne;
    }

    public function getPlugin(): int
    {
        return $this->kPlugin;
    }

    public function getName(): ?string
    {
        return $this->cName;
    }

    public function getDateiname(): ?string
    {
        return $this->cDateiname;
    }

    public function getKopfzeile(): ?string
    {
        return $this->cKopfzeile;
    }

    public function getContent(): ?string
    {
        return $this->cContent;
    }

    public function getFusszeile(): ?string
    {
        return $this->cFusszeile;
    }

    public function getKodierung(): ?string
    {
        return $this->cKodierung;
    }

    public function getSpecial(): ?int
    {
        return $this->nSpecial;
    }

    public function getVarKombiOption(): ?int
    {
        return $this->nVarKombiOption;
    }

    public function getSplitgroesse(): ?int
    {
        return $this->nSplitgroesse;
    }

    public function getZuletztErstellt(): ?string
    {
        return $this->dZuletztErstellt;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function getExportProductCount(): int
    {
        return 0;
    }

    public function getQueue(): ?QueueEntry
    {
        return $this->queue;
    }

    public function useCache(): bool
    {
        return $this->nUseCache === 1;
    }

    public function setCaching(int $caching): self
    {
        $this->nUseCache = $caching;

        return $this;
    }

    public function getCaching(): int
    {
        return $this->nUseCache;
    }

    public function startExport(): bool
    {
        return false;
    }

    public function check(): bool
    {
        return true;
    }

    private static function getHTMLState(int $error): string
    {
        try {
            return Shop::Smarty()->assign('exportformat', (object)['nFehlerhaft' => $error])
                ->fetch('snippets/exportformat_state.tpl');
        } catch (SmartyException | Exception) {
            return '';
        }
    }

    private static function stripMessage(string $out, string $message): string
    {
        $message = \strip_tags($message);
        // strip possible call stack
        if (\preg_match('/(Stack trace|Call Stack):/', $message, $hits)) {
            $callstackPos = \mb_strpos($message, $hits[0]);
            if ($callstackPos !== false) {
                $message = \mb_substr($message, 0, $callstackPos);
            }
        }
        $errText  = '';
        $fatalPos = \mb_strlen($out);
        // strip smarty output if fatal error occurs
        if (\preg_match('/((Recoverable )?Fatal error|Uncaught Error):/ui', $out, $hits)) {
            $fatalPos = \mb_strpos($out, $hits[0]);
            if ($fatalPos !== false) {
                $errText = \mb_substr($out, 0, $fatalPos);
            }
        }
        // strip possible error position from smarty output
        $errText = (string)\preg_replace('/[\t\n]/', ' ', \mb_substr($errText, 0, $fatalPos));
        $len     = \mb_strlen($errText);
        if ($len > 75) {
            $errText = '...' . \mb_substr($errText, $len - 75);
        }

        return \htmlentities($message) . ($len > 0 ? '<br/>on line: ' . \htmlentities($errText) : '');
    }

    public static function ioCheckSyntax(): stdClass
    {
        return (object)[
            'result'  => 'fail',
            'message' => 'Class is no longer supported.',
        ];
    }

    public function checkSyntax(): bool
    {
        return false;
    }

    public function doCheckSyntax(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function checkAll(): array
    {
        return [];
    }

    public function updateError(): void
    {
    }
}
