<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Repositories;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Shop;
use stdClass;

/**
 * Class PendingOrdersRepository
 * @package Plugin\jtl_paypal_commerce\Repositories
 */
class PendingOrdersRepository extends AbstractPluginRepository
{
    private CryptoServiceInterface $cryptoService;

    /**
     * @inheritDoc
     */
    public function __construct(DbInterface $db, ?CryptoServiceInterface $cryptoService = null)
    {
        parent::__construct($db);

        $this->cryptoService = $cryptoService ?? Shop::Container()->getCryptoService();
    }

    public function getTableName(): string
    {
        return '';
    }

    private function getPendingOrdersBaseSQL(bool $debug = false): string
    {
        return "FROM tbestellung
                    INNER JOIN trechnungsadresse ON trechnungsadresse.kRechnungsadresse = tbestellung.kRechnungsadresse
                    INNER JOIN tzahlungsid ON tzahlungsid.kBestellung = tbestellung.kBestellung
                    INNER JOIN tzahlungsart ON tzahlungsart.kZahlungsart = tzahlungsid.kZahlungsart
                    INNER JOIN tpluginzahlungsartklasse ON tpluginzahlungsartklasse.cModulId = tzahlungsart.cModulId
                    LEFT JOIN tzahlungseingang ON tzahlungseingang.kBestellung = tbestellung.kBestellung
                    LEFT JOIN tzahlungsession ON tzahlungsession.kBestellung = tbestellung.kBestellung
                WHERE tpluginzahlungsartklasse.kPlugin = :pluginId
                    AND tzahlungseingang.kBestellung IS NULL
                    AND tbestellung.cAbgeholt = 'P'
                    AND tzahlungsid.txn_id != ''
                    " . ($debug ? '' : 'AND DATE_ADD(tbestellung.dErstellt, INTERVAL 3 HOUR) < NOW()');
    }

    public function hasPendingOrders(int $pluginId, bool $debug = false): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(tbestellung.kBestellung) AS cnt ' . $this->getPendingOrdersBaseSQL($debug),
            'cnt',
            ['pluginId' => $pluginId]
        );
    }

    public function getPendingOrders(int $pluginId, bool $debug = false): Collection
    {
        return $this->db->getCollection(
            'SELECT tbestellung.kBestellung, tbestellung.dErstellt, tbestellung.cBestellNr,
                       trechnungsadresse.kKunde, trechnungsadresse.cNachname, trechnungsadresse.cVorname,
                       tzahlungsart.kZahlungsart, tzahlungsart.cName AS paymentName,
                       tzahlungsid.txn_id, tzahlungsession.cZahlungsID
            ' . $this->getPendingOrdersBaseSQL($debug)
              . 'ORDER BY tbestellung.kBestellung',
            ['pluginId' => $pluginId]
        )->map(function (stdClass $item) {
            $item->customerName = \trim(
                \trim($this->cryptoService->decryptXTEA($item->cNachname ?? '')) . ', ' . $item->cVorname,
                ', '
            );

            return $item;
        });
    }

    public function getInvoiceID(string $txnId): string
    {
        $invoice = $this->db->getSingleObject(
            'SELECT tzahlungsid.kBestellung, tbestellung.cBestellNr
                    FROM tzahlungsid
                    LEFT JOIN tbestellung ON tbestellung.kBestellung = tzahlungsid.kBestellung
                    WHERE tzahlungsid.txn_id = :orderId',
            ['orderId' => $txnId]
        );

        return $invoice->cBestellNr ?? '';
    }

    public function deletePaymentFromOrder(string $txnId): int
    {
        return $this->db->delete('tzahlungsid', 'txn_id', $txnId);
    }
}
