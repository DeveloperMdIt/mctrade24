<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Mappers;

use JTL\Language\LanguageHelper;

class LanguageMapper {

    public const ORDER_LANGUAGE_LOCALE_TO_ISO = [
        'de-de' => 'ger',
        'de-at' => 'ger',
        'de-li' => 'ger',
        'de-lu' => 'ger',
        'de-ch' => 'ger',
        'en-au' => 'eng',
        'en-ca' => 'eng',
        'en-ie' => 'eng',
        'en-gb' => 'eng',
        'en-us' => 'eng',
        'fr-be' => 'fre',
        'fr-ca' => 'fre',
        'fr-ch' => 'fre',
        'fr-fr' => 'fre',
        'fr-lu' => 'fre',
        'it-ch' => 'ita',
        'it-it' => 'ita',
        'es-es' => 'spa'
    ];



    public static function AmazonOrderLanguageToJtlIso($orderLanguage) {
        if(array_key_exists(mb_strtolower($orderLanguage), self::ORDER_LANGUAGE_LOCALE_TO_ISO)) {
            $possibleIso = self::ORDER_LANGUAGE_LOCALE_TO_ISO[mb_strtolower($orderLanguage)];
            if(LanguageHelper::getLangIDFromIso($possibleIso) !== null) {
                return $possibleIso;
            }
        }
        return LanguageHelper::getDefaultLanguage(true)->cISO;
    }
}