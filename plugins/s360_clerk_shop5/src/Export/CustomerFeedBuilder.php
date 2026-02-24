<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Events\Dispatcher;
use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Utils\Config;

class CustomerFeedBuilder extends AbstractFeedBuilder
{
    public const EVENT_PROCESS_ROW = 'process_customers_row';
    public const EVENT_GET_QUERY = 'get_customers_query';

    public function processRow(array $row): array
    {
        $row['lastname'] = trim(Shop::Container()->getCryptoService()->decryptXTEA($row['lastname']));
        $row['active'] = (bool) $row['active'];
        $row['is_b2b'] = (bool) $row['is_b2b'];
        $row['registered'] = $row['registered'] === 'Y';
        $row['subscribed'] = $row['subscribed'] === 'Y';
        $row['name'] = $row['firstname'] . ' ' . $row['lastname'];

        $row['gender'] = match ($row['gender']) {
            'Mr' => 'male',
            'Mrs' => 'female',
            default => null,
        };

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
                kKunde as id, kKundengruppe as customergroup, cVorname as firstname, cNachname as lastname,
                cMail as email, cPLZ as zip, cKundenNr as customernumber, cNewsletter as subscribed,
                cAktiv as active, cFirma as is_b2b, nRegistriert as registered, cAnrede as gender,
            ROUND(datediff(CURDATE(), tkunde.dGeburtstag) / 365.25, 0) as `age`
            FROM `tkunde`
            WHERE tkunde.cAktiv = 'Y' AND tkunde.kSprache = {$this->store->getLanguageId()} AND tkunde.kKundengruppe = {$this->store->getCustomerGroupId()}";

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_GET_QUERY,
            ['query' => &$query, 'builder' => $this]
        );

        return $query;
    }
}
