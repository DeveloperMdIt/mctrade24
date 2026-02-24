<?php

declare(strict_types=1);

namespace JTL\Mail\Hydrator;

use DateTime;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferschein;
use JTL\Checkout\Versand;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Helpers\Date;
use JTL\Language\LanguageModel;
use JTL\Shipping\Services\ShippingService;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use stdClass;

/**
 * Class TestHydrator
 * @package JTL\Mail\Hydrator
 */
class TestHydrator extends DefaultsHydrator
{
    private ShippingService $shippingService;
    private string $currentYear;

    public function __construct(
        JTLSmarty $smarty,
        DbInterface $db,
        Shopsetting $settings,
        ?ShippingService $shippingService = null,
    ) {
        parent::__construct($smarty, $db, $settings);
        $this->shippingService = $shippingService ?? Shop::Container()->getShippingService();
        $this->currentYear     = \date('Y');
    }

    /**
     * @inheritdoc
     */
    public function hydrate(?object $data, LanguageModel $language): void
    {
        parent::hydrate($data, $language);
        Shop::Lang()->setzeSprache($language->getCode());
        $langID        = $language->getId();
        $msg           = $this->getMessage();
        $customerBonus = $this->getBonus();
        $customerGroup = (new CustomerGroup(0, $this->db))->loadDefaultGroup();
        $order         = $this->getOrder($langID);
        $customer      = $this->getCustomer($langID, $customerGroup->getID());
        $checkbox      = $this->getCheckbox();
        $argwrb        = $this->db->select(
            'ttext',
            ['kKundengruppe', 'kSprache'],
            [$customer->kKundengruppe, $langID]
        );

        $this->smarty->assign('oKunde', $customer)
            ->assign('oMailObjekt', $this->getStatusMail())
            ->assign('Verfuegbarkeit_arr', ['cArtikelName_arr' => [], 'cHinweis' => ''])
            ->assign('BestandskundenBoni', (object)['fGuthaben' => Preise::getLocalizedPriceString(1.23)])
            ->assign('cAnzeigeOrt', 'Example')
            ->assign('oSprache', $language)
            ->assign('oCheckBox', $checkbox)
            ->assign('Kunde', $customer)
            ->assign('Kundengruppe', $customerGroup)
            ->assign('cAnredeLocalized', Shop::Lang()->get('salutationM'))
            ->assign('Bestellung', $order)
            ->assign('Neues_Passwort', 'geheim007')
            ->assign(
                'passwordResetLink',
                Shop::Container()->getLinkService()->getStaticRoute('pass.php')
                . '?fpwh=ca68b243f0c1e7e57162055f248218fd'
            )
            ->assign('Gutschein', $this->getGift())
            ->assign('interval', 720)
            ->assign('intervalLoc', 'Monatliche Status-Email')
            ->assign('AGB', $argwrb)
            ->assign('WRB', $argwrb)
            ->assign('DSE', $argwrb)
            ->assign('URL_SHOP', Shop::getURL() . '/')
            ->assign('Kupon', $this->getCoupon())
            ->assign('Optin', $this->getOptin())
            ->assign('couponTypes', Kupon::getCouponTypes())
            ->assign('Nachricht', $msg)
            ->assign('Artikel', $this->getProduct())
            ->assign('Wunschliste', $this->getWishlist())
            ->assign('VonKunde', $customer)
            ->assign('Benachrichtigung', $this->getAvailabilityMessage())
            ->assign('NewsletterEmpfaenger', $this->getNewsletterRecipient($langID))
            ->assign('oBewertungGuthabenBonus', $customerBonus);
    }

    private function getStatusMail(): stdClass
    {
        $mail                                           = new stdClass();
        $mail->mail                                     = new stdClass();
        $mail->oAnzahlArtikelProKundengruppe            = 1;
        $mail->nAnzahlNeukunden                         = 21;
        $mail->nAnzahlNeukundenGekauft                  = 33;
        $mail->nAnzahlBestellungen                      = 17;
        $mail->nAnzahlBestellungenNeukunden             = 13;
        $mail->nAnzahlBesucher                          = 759;
        $mail->nAnzahlBesucherSuchmaschine              = 165;
        $mail->nAnzahlBewertungen                       = 99;
        $mail->nAnzahlBewertungenNichtFreigeschaltet    = 15;
        $mail->nAnzahlVersendeterBestellungen           = 15;
        $mail->oAnzahlGezahltesGuthaben                 = -1;
        $mail->nAnzahlGeworbenerKunden                  = 11;
        $mail->nAnzahlErfolgreichGeworbenerKunden       = 0;
        $mail->nAnzahlVersendeterWunschlisten           = 0;
        $mail->nAnzahlNewskommentare                    = 21;
        $mail->nAnzahlNewskommentareNichtFreigeschaltet = 11;
        $mail->nAnzahlProduktanfrageArtikel             = 1;
        $mail->nAnzahlProduktanfrageVerfuegbarkeit      = 2;
        $mail->nAnzahlVergleiche                        = 3;
        $mail->nAnzahlGenutzteKupons                    = 4;
        $mail->nAnzahlZahlungseingaengeVonBestellungen  = 5;
        $mail->nAnzahlNewsletterAbmeldungen             = 6;
        $mail->nAnzahlNewsletterAnmeldungen             = 6;
        $mail->dVon                                     = '01.01.' . $this->currentYear;
        $mail->dBis                                     = '31.01.' . $this->currentYear;
        $mail->oLogEntry_arr                            = [];
        $mail->cIntervall                               = 'Monatliche Status-Email';

        return $mail;
    }

    private function getCheckbox(): CheckBox
    {
        $id = $this->db->getSingleInt('SELECT kCheckbox FROM tcheckbox LIMIT 1', 'kCheckbox');

        return new CheckBox($id, $this->db);
    }

    private function getAvailabilityMessage(): stdClass
    {
        $msg            = new stdClass();
        $msg->cVorname  = 'Max';
        $msg->cNachname = 'Musterman';

        return $msg;
    }

    private function getGift(): stdClass
    {
        $gift                 = new stdClass();
        $gift->fWert          = 5.00;
        $gift->cLocalizedWert = '5,00 EUR';
        $gift->cGrund         = 'Geburtstag';
        $gift->kGutschein     = 33;
        $gift->kKunde         = 1;

        return $gift;
    }

    private function getMessage(): stdClass
    {
        $msg                   = new stdClass();
        $msg->cNachricht       = 'Lorem ipsum dolor sit amet.';
        $msg->cAnrede          = 'm';
        $msg->cAnredeLocalized = Shop::Lang()->get('salutationM');
        $msg->cVorname         = 'Max';
        $msg->cNachname        = 'Mustermann';
        $msg->cFirma           = 'Musterfirma';
        $msg->cMail            = 'info@example.com';
        $msg->cFax             = '34782034';
        $msg->cTel             = '34782035';
        $msg->cMobil           = '34782036';
        $msg->cBetreff         = 'Allgemeine Anfrage';

        return $msg;
    }

    private function getWishlist(): stdClass
    {
        $wishlist                      = new stdClass();
        $wishlist->kWunschlsite        = 5;
        $wishlist->kKunde              = 1480;
        $wishlist->cName               = 'Wunschzettel';
        $wishlist->nStandard           = 1;
        $wishlist->nOeffentlich        = 0;
        $wishlist->cURLID              = '5686f6vv6c86v65nv6m8';
        $wishlist->dErstellt           = '2019-01-01 01:01:01';
        $wishlist->CWunschlistePos_arr = [];

        $item                                 = new stdClass();
        $item->kWunschlistePos                = 3;
        $item->kWunschliste                   = 5;
        $item->kArtikel                       = 261;
        $item->cArtikelName                   = 'Hansu Televsion';
        $item->fAnzahl                        = 2;
        $item->cKommentar                     = 'Television';
        $item->dHinzugefuegt                  = '2019-07-12 13:55:11';
        $item->Artikel                        = new stdClass();
        $item->Artikel->cName                 = 'LAN Festplatte IPDrive';
        $item->Artikel->cEinheit              = 'Stck.';
        $item->Artikel->fPreis                = 368.1069;
        $item->Artikel->fMwSt                 = 19;
        $item->Artikel->nAnzahl               = 1;
        $item->Artikel->cURL                  = 'LAN-Festplatte-IPDrive';
        $item->Artikel->Bilder                = [];
        $item->Artikel->Bilder[0]             = new stdClass();
        $item->Artikel->Bilder[0]->cPfadKlein = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $item->CWunschlistePosEigenschaft_arr = [];

        $wishlist->CWunschlistePos_arr[] = $item;

        $item                                 = new stdClass();
        $item->kWunschlistePos                = 4;
        $item->kWunschliste                   = 5;
        $item->kArtikel                       = 262;
        $item->cArtikelName                   = 'Hansu Phone';
        $item->fAnzahl                        = 1;
        $item->cKommentar                     = 'Phone';
        $item->dHinzugefuegt                  = '2019-07-12 13:55:18';
        $item->Artikel                        = new stdClass();
        $item->Artikel->cName                 = 'USB Connector';
        $item->Artikel->cEinheit              = 'Stck.';
        $item->Artikel->fPreis                = 89.90;
        $item->Artikel->fMwSt                 = 19;
        $item->Artikel->nAnzahl               = 1;
        $item->Artikel->cURL                  = 'USB-Connector';
        $item->Artikel->Bilder                = [];
        $item->Artikel->Bilder[0]             = new stdClass();
        $item->Artikel->Bilder[0]->cPfadKlein = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        $item->CWunschlistePosEigenschaft_arr = [];

        $attr                                   = new stdClass();
        $attr->kWunschlistePosEigenschaft       = 2;
        $attr->kWunschlistePos                  = 4;
        $attr->kEigenschaft                     = 2;
        $attr->kEigenschaftWert                 = 3;
        $attr->cFreifeldWert                    = '';
        $attr->cEigenschaftName                 = 'Farbe';
        $attr->cEigenschaftWertName             = 'rot';
        $item->CWunschlistePosEigenschaft_arr[] = $attr;

        $wishlist->CWunschlistePos_arr[] = $item;

        return $wishlist;
    }

    private function getCoupon(): stdClass
    {
        $now                           = (new DateTime())->format('Y-m-d H:i:s');
        $until                         = (new DateTime())->modify('+28 days')->format('Y-m-d H:i:s');
        $coupon                        = new stdClass();
        $coupon->cName                 = 'Kuponname';
        $coupon->Hersteller            = [];
        $coupon->fWert                 = 5;
        $coupon->cWertTyp              = 'festpreis';
        $coupon->dGueltigAb            = $now;
        $coupon->cGueltigAbLong        = $now;
        $coupon->GueltigAb             = $now;
        $coupon->dGueltigBis           = $until;
        $coupon->cGueltigBisLong       = $until;
        $coupon->GueltigBis            = $until;
        $coupon->cCode                 = 'geheimcode';
        $coupon->nVerwendungen         = 100;
        $coupon->nVerwendungenProKunde = 2;
        $coupon->AngezeigterName       = 'lokalisierter Name des Kupons';
        $coupon->cKuponTyp             = Kupon::TYPE_STANDARD;
        $coupon->cLocalizedWert        = '5 EUR';
        $coupon->cLocalizedMBW         = '100,00 EUR';
        $coupon->fMindestbestellwert   = 100;
        $coupon->Artikel               = [];
        $coupon->Artikel[0]            = new stdClass();
        $coupon->Artikel[1]            = new stdClass();
        $coupon->Artikel[0]->cName     = 'Artikel eins';
        $coupon->Artikel[0]->cURL      = 'http://example.com/artikel1';
        $coupon->Artikel[0]->cURLFull  = 'http://example.com/artikel1';
        $coupon->Artikel[1]->cName     = 'Artikel zwei';
        $coupon->Artikel[1]->cURL      = 'http://example.com/artikel2';
        $coupon->Artikel[1]->cURLFull  = 'http://example.com/artikel2';
        $coupon->Kategorien            = [];
        $coupon->Kategorien[0]         = new stdClass();
        $coupon->Kategorien[1]         = new stdClass();
        $coupon->Kategorien[0]->cName  = 'Kategorie eins';
        $coupon->Kategorien[0]->cURL   = 'http://example.com/kat1';
        $coupon->Kategorien[1]->cName  = 'Kategorie zwei';
        $coupon->Kategorien[1]->cURL   = 'http://example.com/kat2';

        return $coupon;
    }

    private function getCustomer(int $langID, int $customerGroupID): stdClass
    {
        $customer                    = new stdClass();
        $customer->fRabatt           = 0.00;
        $customer->fGuthaben         = 0.00;
        $customer->cAnrede           = 'm';
        $customer->Anrede            = 'Herr';
        $customer->cAnredeLocalized  = Shop::Lang()->get('salutationM');
        $customer->cTitel            = 'Dr.';
        $customer->cVorname          = 'Max';
        $customer->cNachname         = 'Mustermann';
        $customer->cFirma            = 'Musterfirma';
        $customer->cZusatz           = 'Musterfirma-Zusatz';
        $customer->cStrasse          = 'Musterstrasse';
        $customer->cHausnummer       = '123';
        $customer->cPLZ              = '12345';
        $customer->cOrt              = 'Musterstadt';
        $customer->cLand             = 'Musterland ISO';
        $customer->cTel              = '12345678';
        $customer->cFax              = '98765432';
        $customer->cMail             = $this->settings['emails']['email_master_absender'];
        $customer->cUSTID            = 'ust234';
        $customer->cBundesland       = 'NRW';
        $customer->cAdressZusatz     = 'Linker Hof';
        $customer->cMobil            = '01772322234';
        $customer->dGeburtstag       = '1981-10-10';
        $customer->cWWW              = 'http://example.com';
        $customer->kKundengruppe     = $customerGroupID;
        $customer->kSprache          = $langID;
        $customer->cPasswortKlartext = 'superGeheim';
        $customer->angezeigtesLand   = 'Musterland';

        return $customer;
    }

    private function getOrder(int $languageID): stdClass
    {
        $order             = $this->getOrderBaseData($languageID);
        $order->Positionen = [];
        foreach ($this->getOrderItems() as $item) {
            $order->Positionen[] = $item;
        }
        $order->Steuerpositionen                     = [];
        $order->Steuerpositionen[0]                  = new stdClass();
        $order->Steuerpositionen[0]->cName           = 'inkl. 19% USt.';
        $order->Steuerpositionen[0]->fUst            = 19;
        $order->Steuerpositionen[0]->fBetrag         = 98.04;
        $order->Steuerpositionen[0]->cPreisLocalized = '98,04 EUR';

        $order->Waehrung = $this->getOrderCurrency();

        $order->Zahlungsart           = new stdClass();
        $order->Zahlungsart->cName    = 'Rechnung';
        $order->Zahlungsart->cModulId = 'za_rechnung_jtl';

        $order->Zahlungsinfo  = $this->getOrderPaymentInfo();
        $order->Lieferadresse = $this->getOrderDeliveryAddress();

        $order->oRechnungsadresse = $order->Lieferadresse;

        $deliveryNote = new Lieferschein();
        $deliveryNote->setEmailVerschickt(false);
        $deliveryNote->oVersand_arr = [];
        $shipping                   = new Versand();
        $shipping->setLogistikURL(
            'https://nolp.dhl.de/nextt-online-public/'
            . 'report_popup.jsp?lang=de&zip=#PLZ#&idc=#IdentCode#'
        );
        $shipping->setIdentCode('123456');
        $deliveryNote->oVersand_arr[] = $shipping;
        $deliveryNote->oPosition_arr  = [];
        foreach ($this->getOrderItems() as $item) {
            $deliveryNote->oPosition_arr[] = $item;
        }
        $order->oLieferschein_arr   = [];
        $order->oLieferschein_arr[] = $deliveryNote;

        $order->oEstimatedDelivery->localized = $this->shippingService->getDeliverytimeEstimationText(
            $order->oEstimatedDelivery->longestMin,
            $order->oEstimatedDelivery->longestMax
        );

        $start = Date::dateAddWeekday(
            $order->dErstellt,
            $order->oEstimatedDelivery->longestMin
        )->format('d.m.Y');
        $end   = Date::dateAddWeekday(
            $order->dErstellt,
            $order->oEstimatedDelivery->longestMax
        )->format('d.m.Y');

        $order->cEstimatedDeliveryEx = $start . ' - ' . $end;

        return $order;
    }

    private function getOrderBaseData(int $languageID): stdClass
    {
        $order                         = new stdClass();
        $order->kWaehrung              = $languageID;
        $order->kSprache               = 1;
        $order->fGuthaben              = '5.0000';
        $order->fGesamtsumme           = '433.00';
        $order->cBestellNr             = 'Prefix-3432-Suffix';
        $order->cVersandInfo           = 'Optionale Information zum Versand';
        $order->cTracking              = 'Track232837';
        $order->cKommentar             = 'Kundenkommentar zur Bestellung';
        $order->cVersandartName        = 'DHL bis 10kg';
        $order->cZahlungsartName       = 'Nachnahme';
        $order->cStatus                = 1;
        $order->dVersandDatum          = $this->currentYear . '-10-21';
        $order->dErstellt              = $this->currentYear . '-10-12 09:28:38';
        $order->dBezahltDatum          = $this->currentYear . '-10-20';
        $order->cLogistiker            = 'DHL';
        $order->cTrackingURL           = 'https://dhl.de/linkzudhl.php';
        $order->dVersanddatum_de       = '21.10.' . $this->currentYear;
        $order->dBezahldatum_de        = '20.10.' . $this->currentYear;
        $order->dErstelldatum_de       = '12.10.' . $this->currentYear;
        $order->dVersanddatum_en       = '21st October ' . $this->currentYear;
        $order->dBezahldatum_en        = '20th October ' . $this->currentYear;
        $order->dErstelldatum_en       = '12th October ' . $this->currentYear;
        $order->cBestellwertLocalized  = '511,00 EUR';
        $order->GuthabenNutzen         = 1;
        $order->GutscheinLocalized     = '5,00 EUR';
        $order->fWarensumme            = 433.004004;
        $order->fVersand               = 0;
        $order->nZahlungsTyp           = 0;
        $order->fWaehrungsFaktor       = 1;
        $order->WarensummeLocalized[0] = '511,00 EUR';
        $order->WarensummeLocalized[1] = '429,41 EUR';
        $order->oEstimatedDelivery     = (object)[
            'localized'  => '',
            'longestMin' => 3,
            'longestMax' => 6,
        ];
        $order->cEstimatedDelivery     = &$order->oEstimatedDelivery->localized;

        return $order;
    }

    private function getOrderCurrency(): stdClass
    {
        $currency                       = new stdClass();
        $currency->cISO                 = 'EUR';
        $currency->cName                = 'EUR';
        $currency->cNameHTML            = '&euro;';
        $currency->fFaktor              = 1;
        $currency->cStandard            = 'Y';
        $currency->cVorBetrag           = 'N';
        $currency->cTrennzeichenCent    = ',';
        $currency->cTrennzeichenTausend = '.';

        return $currency;
    }

    private function getOrderPaymentInfo(): stdClass
    {
        $info               = new stdClass();
        $info->cBankName    = 'Bankname';
        $info->cBLZ         = '3443234';
        $info->cKontoNr     = 'Kto12345';
        $info->cIBAN        = 'IB239293';
        $info->cBIC         = 'BIC3478';
        $info->cKartenNr    = 'KNR4834';
        $info->cGueltigkeit = '20.10.' . $this->currentYear;
        $info->cCVV         = '1234';
        $info->cKartenTyp   = 'VISA';
        $info->cInhaber     = 'Max Mustermann';

        return $info;
    }

    private function getOrderDeliveryAddress(): stdClass
    {
        $address                   = new stdClass();
        $address->kLieferadresse   = 1;
        $address->cAnrede          = 'm';
        $address->cAnredeLocalized = Shop::Lang()->get('salutationM');
        $address->cVorname         = 'John';
        $address->cNachname        = 'Doe';
        $address->cStrasse         = 'Musterlieferstr.';
        $address->cHausnummer      = '77';
        $address->cAdressZusatz    = '2. Etage';
        $address->cPLZ             = '12345';
        $address->cOrt             = 'Musterlieferstadt';
        $address->cBundesland      = 'Lieferbundesland';
        $address->cLand            = 'Lieferland ISO';
        $address->cTel             = '112345678';
        $address->cMobil           = '123456789';
        $address->cFax             = '12345678909';
        $address->cMail            = 'john.doe@example.com';
        $address->angezeigtesLand  = 'Lieferland';

        return $address;
    }

    /**
     * @return stdClass[]
     */
    private function getOrderItems(): array
    {
        $items                          = [];
        $item                           = new stdClass();
        $item->kArtikel                 = 1;
        $item->cName                    = 'LAN Festplatte IPDrive';
        $item->cArtNr                   = 'AF8374';
        $item->cEinheit                 = 'Stk.';
        $item->cLieferstatus            = '3-4 Tage';
        $item->fPreisEinzelNetto        = 111.2069;
        $item->fPreis                   = 368.1069;
        $item->fMwSt                    = 19;
        $item->nAnzahl                  = 2;
        $item->nPosTyp                  = 1;
        $item->cHinweis                 = 'Hinweistext zum Artikel';
        $item->cGesamtpreisLocalized[0] = '278,00 EUR';
        $item->cGesamtpreisLocalized[1] = '239,66 EUR';
        $item->cEinzelpreisLocalized[0] = '139,00 EUR';
        $item->cEinzelpreisLocalized[1] = '119,83 EUR';

        $item->WarenkorbPosEigenschaftArr                           = [];
        $item->WarenkorbPosEigenschaftArr[0]                        = new stdClass();
        $item->WarenkorbPosEigenschaftArr[0]->cEigenschaftName      = 'Kapazität';
        $item->WarenkorbPosEigenschaftArr[0]->cEigenschaftWertName  = '4000GB';
        $item->WarenkorbPosEigenschaftArr[0]->fAufpreis             = 128.45;
        $item->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[0] = '149,00 EUR';
        $item->WarenkorbPosEigenschaftArr[0]->cAufpreisLocalized[1] = '128,45 EUR';

        $nextYear                  = \date('Y', \strtotime('+1 year'));
        $item->nAusgeliefert       = 1;
        $item->nAusgeliefertGesamt = 1;
        $item->nOffenGesamt        = 1;
        $item->dMHD                = $nextYear . '-01-01';
        $item->dMHD_de             = '01.01.' . $nextYear;
        $item->cChargeNr           = 'A2100698.b12';
        $item->cSeriennummer       = '465798132756';

        $items[] = $item;

        $item                           = new stdClass();
        $item->kArtikel                 = 2;
        $item->cName                    = 'Klappstuhl';
        $item->cArtNr                   = 'KS332';
        $item->cEinheit                 = 'Stck.';
        $item->cLieferstatus            = '1 Woche';
        $item->fPreisEinzelNetto        = 100;
        $item->fPreis                   = 200;
        $item->fMwSt                    = 19;
        $item->nAnzahl                  = 1;
        $item->nPosTyp                  = 2;
        $item->cHinweis                 = 'Hinweistext zum Artikel';
        $item->cGesamtpreisLocalized[0] = '238,00 EUR';
        $item->cGesamtpreisLocalized[1] = '200,00 EUR';
        $item->cEinzelpreisLocalized[0] = '238,00 EUR';
        $item->cEinzelpreisLocalized[1] = '200,00 EUR';

        $item->nAusgeliefert       = 1;
        $item->nAusgeliefertGesamt = 1;
        $item->nOffenGesamt        = 0;

        $items[] = $item;

        return $items;
    }

    private function getNewsletterRecipient(int $languageID): stdClass
    {
        $recipient                     = new stdClass();
        $recipient->kSprache           = $languageID;
        $recipient->kKunde             = null;
        $recipient->nAktiv             = 0;
        $recipient->cAnrede            = 'w';
        $recipient->cVorname           = 'Erika';
        $recipient->cNachname          = 'Mustermann';
        $recipient->cEmail             = 'test@example.com';
        $recipient->cOptCode           = 'acc4cedb690aed6161d6034417925b97f2';
        $recipient->cLoeschCode        = 'dc1338521613c3cfeb1988261029fe3058';
        $recipient->dEingetragen       = 'NOW()';
        $recipient->dLetzterNewsletter = '_DBNULL_';
        $recipient->cLoeschURL         = Shop::getURL() . '/?'
            . \QUERY_PARAM_OPTIN_CODE . '=' . $recipient->cLoeschCode;
        $recipient->cFreischaltURL     = Shop::getURL() . '/?'
            . \QUERY_PARAM_OPTIN_CODE . '=' . $recipient->cOptCode;

        return $recipient;
    }

    private function getProduct(): stdClass
    {
        $product                    = new stdClass();
        $product->cName             = 'LAN Festplatte IPDrive';
        $product->cArtNr            = 'AF8374';
        $product->cEinheit          = 'Stk.';
        $product->cLieferstatus     = '3-4 Tage';
        $product->fPreisEinzelNetto = 111.2069;
        $product->fPreis            = 368.1069;
        $product->fMwSt             = 19;
        $product->nAnzahl           = 1;
        $product->cURL              = 'LAN-Festplatte-IPDrive';

        return $product;
    }

    private function getBonus(): stdClass
    {
        $bonus                          = new stdClass();
        $bonus->kKunde                  = 1379;
        $bonus->fGuthaben               = '2,00 &euro';
        $bonus->nBonuspunkte            = 0;
        $bonus->dErhalten               = 'NOW()';
        $bonus->fGuthabenBonusLocalized = Preise::getLocalizedPriceString(2.00);

        return $bonus;
    }

    private function getOptin(): stdClass
    {
        $optin                  = new stdClass();
        $optin->activationURL   = 'http://example.com/testproduct?' . \QUERY_PARAM_OPTIN_CODE . '=ac123456789';
        $optin->deactivationURL = 'http://example.com/testproduct?' . \QUERY_PARAM_OPTIN_CODE . '=dc123456789';

        return $optin;
    }
}
