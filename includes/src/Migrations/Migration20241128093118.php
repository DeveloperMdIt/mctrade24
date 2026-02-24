<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20241128093118
 */
class Migration20241128093118 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'dr';
    }

    public function getDescription(): string
    {
        return 'add ms oauth mail method settings';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $kEinstellungenConf = $this->fetchOne(
            "SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'email_methode'"
        )->kEinstellungenConf;

        $this->execute(
            'INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort)
            VALUE (' . $kEinstellungenConf . ", 'Microsoft Outlook via OAuth', 'outlook', 5)"
        );

        $this->setConfig(
            configName: 'oauth_client_id',
            configValue: '',
            configSectionID: \CONF_EMAILS,
            externalName: 'OAuth Anwendungs-ID (Client ID)',
            inputType: 'text',
            sort: 122,
            additionalProperties: (object)[
                'cBeschreibung' => 'Hier legen Sie die Client ID für die Anmeldung via OAuth fest'
            ]
        );

        $this->setConfig(
            configName: 'oauth_client_secret',
            configValue: '',
            configSectionID: \CONF_EMAILS,
            externalName: 'OAuth Client-Geheimnis',
            inputType: 'text',
            sort: 123,
            additionalProperties: (object)[
                'cBeschreibung' => 'Hier legen Sie den geheimen Client-Schlüssel für die Anmeldung via OAuth fest'
            ]
        );

        $this->setConfig(
            configName: 'oauth_tenant_id',
            configValue: '',
            configSectionID: \CONF_EMAILS,
            externalName: 'OAuth Mandanten-ID',
            inputType: 'text',
            sort: 124,
            additionalProperties: (object)[
                'cBeschreibung' => 'Hier legen Sie die Mandanten-ID für die Anmeldung via OAuth fest'
            ]
        );

        $this->setConfig(
            configName: 'oauth_refresh_token',
            configValue: '',
            configSectionID: \CONF_EMAILS,
            externalName: 'OAuth Refresh-Token',
            inputType: 'text',
            sort: 124,
            additionalProperties: (object)[
                'cBeschreibung' => 'Diesen Wert erhalten Sie automatisch'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $kEinstellungenConf = $this->fetchOne(
            "SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'email_methode'"
        )->kEinstellungenConf;

        $this->execute(
            'DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = ' . $kEinstellungenConf
            . " AND cWert = 'outlook'"
        );

        $this->removeConfig('oauth_client_id');
        $this->removeConfig('oauth_client_secret');
        $this->removeConfig('oauth_tenant_id');
        $this->removeConfig('oauth_refresh_token');
    }
}
