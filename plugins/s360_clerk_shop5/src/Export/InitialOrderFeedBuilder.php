<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Events\Dispatcher;
use Plugin\s360_clerk_shop5\src\Utils\Config;

class InitialOrderFeedBuilder extends AbstractFeedBuilder
{
    public const EVENT_PROCESS_ROW = 'process_sales_row';
    public const EVENT_GET_QUERY = 'get_sales_query';

    public function getTotalOrders(): int
    {
        $result = $this->connection->query(
            "SELECT COUNT(orders.cBestellNr) as total
            FROM (SELECT kBestellung, kSprache, kKunde, cBestellNr FROM tbestellung GROUP BY cBestellNr) as orders
            LEFT JOIN tkunde ON orders.kKunde = tkunde.kKunde
            WHERE orders.kSprache = {$this->store->getLanguageId()} AND tkunde.kKundengruppe = {$this->store->getCustomerGroupId()}"
        );

        if ($result->num_rows) {
            return (int) $result->fetch_array()['total'] ?? 0;
        }

        return 0;
    }

    public function processRow(array $row): array
    {
        $products = explode('----', $row['products']);
        $row['products'] = [];

        foreach ($products as $product) {
            list($id, $quantity, $price) = explode(';', $product);
            $row['products'][] = [
                'id' => empty($id) ? -1 : $id,
                'quantity' => empty($quantity) ? 0 : $quantity,
                'price' => empty($price) ? 0 : $price,
            ];
        }

        if ($this->plugin->getConfig()->getValue(Config::SETTING_HASHED_MAILS) == 'on') {
            list($localPart, $domainPart) = explode('@', $row['email'], 2);

            if ($localPart && $domainPart) {
                $row['email'] = md5($localPart) . '@' . $domainPart;
            }
        }

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_PROCESS_ROW,
            ['row' => &$row, 'builder' => $this]
        );

        return $row;
    }

    public function getSqlQuery(): string
    {
        $query = "SELECT
                tbestellung.cBestellNr as id,
                GROUP_CONCAT(
                    CONCAT_WS(
                        ';',twarenkorbpos.kArtikel, CAST(twarenkorbpos.nAnzahl as UNSIGNED), twarenkorbpos.fPreis * (1 + twarenkorbpos.fMwSt/100)
                    ) SEPARATOR '----'
                ) as products,
                UNIX_TIMESTAMP(tbestellung.dErstellt) as time,
                IF (tkunde.cMail IS NULL ,'',cMail) as email,
                IF (tkunde.cKundenNr IS NULL ,'',tkunde.cKundenNr ) as customer
            FROM tbestellung
            LEFT JOIN tkunde ON tbestellung.kKunde = tkunde.kKunde AND tkunde.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN twarenkorbpos ON tbestellung.kWarenkorb = twarenkorbpos.kWarenkorb AND twarenkorbpos.nPosTyp = 1
            WHERE tbestellung.kSprache = {$this->store->getLanguageId()}
            GROUP BY id
            ORDER BY id DESC";

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_GET_QUERY,
            ['query' => &$query, 'builder' => $this]
        );

        return $query;
    }
}
