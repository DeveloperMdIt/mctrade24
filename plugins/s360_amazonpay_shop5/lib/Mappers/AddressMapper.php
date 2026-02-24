<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\lib\Mappers;

use JTL\Checkout\Adresse;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Rechnungsadresse;
use JTL\Language\LanguageHelper;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Address;

/**
 * Class AddressMapper
 * Responsible for the mapping of address data of any kind.
 */
class AddressMapper {

    public const ADDRESS_TYPE_BILLING = 'billing';
    public const ADDRESS_TYPE_SHIPPING = 'shipping';

    /**
     * @param $fullName
     * @return array with keys firstName and lastName
     */
    public static function splitName($fullName): array {
        $parts = mb_split('\s+', $fullName, 2);
        if (empty($parts) || \count($parts) === 1) {
            return ['firstName' => '', 'lastName' => $fullName];
        }
        return ['firstName' => $parts[0], 'lastName' => $parts[1]];

    }

    /**
     * @param Address $address
     * @param string $type
     * @return Adresse
     */
    public static function mapAddressAmazonToJtl(Address $address, string $type = self::ADDRESS_TYPE_BILLING): Adresse {
        if($type === self::ADDRESS_TYPE_BILLING) {
            $result = new Rechnungsadresse();
        } else {
            $result = new Lieferadresse();
        }
        // map name
        if (null !== $address->getName()) {
            $nameParts = self::splitName($address->getName());
            $result->cVorname = $nameParts['firstName'];
            $result->cNachname = $nameParts['lastName'];
        }
        // map address lines
        $addressLine1 = $address->getAddressLine1() ?? '';
        $addressLine2 = $address->getAddressLine2() ?? '';
        $addressLine3 = $address->getAddressLine3() ?? '';

        // heuristical approach as defined by Amazon
        if (!empty($addressLine3)) {
            // if the third address line is set, we interpret this line as street and number, and the first two lines as company part
            $split = mb_split(' ', $addressLine3);
            if (count($split) > 1) {
                $result->cHausnummer = $split[count($split) - 1];
                unset($split[count($split) - 1]);
                $result->cStrasse = implode(' ', $split);
            } else {
                $sStreet = implode(' ', $split);
                if (mb_strlen($sStreet) > 1) {
                    $result->cHausnummer = mb_substr($sStreet, -1);
                    $result->cStrasse = mb_substr($sStreet, 0, -1);
                } else {
                    $result->cHausnummer = '';
                }
            }
            $result->cFirma = trim($addressLine1 . ' ' . $addressLine2);
        } else {
            if (!empty($addressLine2)) {
                // if no 3rd line is set, but the 2nd line is set, we interpret the second line as street and number, and the first line as company part
                $split = mb_split(' ', $addressLine2);
                if (count($split) > 1) {
                    $result->cHausnummer = $split[count($split) - 1];
                    unset($split[count($split) - 1]);
                    $result->cStrasse = implode(' ', $split);
                } else {
                    $sStreet = implode(' ', $split);
                    if (mb_strlen($sStreet) > 1) {
                        $result->cHausnummer = mb_substr($sStreet, -1);
                        $result->cStrasse = mb_substr($sStreet, 0, -1);
                    } else {
                        $result->cHausnummer = '';
                    }
                }
                $result->cFirma = trim($addressLine1);
            } else {
                // only the first line is set, we interpret it as street and number, and no company name
                $split = mb_split(' ', $addressLine1);
                if (count($split) > 1) {
                    $result->cHausnummer = $split[count($split) - 1];
                    unset($split[count($split) - 1]);
                    $result->cStrasse = implode(' ', $split);
                } else {
                    $sStreet = implode(' ', $split);
                    if (mb_strlen($sStreet) > 1) {
                        $result->cHausnummer = mb_substr($sStreet, -1);
                        $result->cStrasse = mb_substr($sStreet, 0, -1);
                    } else {
                        $result->cHausnummer = '';
                    }
                }
                $result->cFirma = '';
            }
        }

        $result->cLand = self::mapCountryCodeAmazonToJtl($address->getCountryCode() ?? '');
        $result->cOrt = $address->getCity() ?? '';
        $result->cPLZ = $address->getPostalCode() ?? '';
        $result->cBundesland = $address->getStateOrRegion() ?? '';
        $result->angezeigtesLand  = LanguageHelper::getCountryCodeByCountryName($result->cLand);

        if(!empty($address->getPhoneNumber())) {
            $result->cTel = preg_replace('/[^\d\-()\/+\s]/', '', $address->getPhoneNumber()); // Make sure to only map "valid" phone number characters as JTL defines them.
        }

        /*
         * The real challenge in mapping the Amazon Pay address is the existence of address line 1 through 3 which have virtually NO semantics, but they do contain,
         * in one way or another, the street, housenumber, company name/additions and address additions
         */
        // map address lines
        $addressLine1 = $address->getAddressLine1() ?? '';
        $addressLine2 = $address->getAddressLine2() ?? '';
        $addressLine3 = $address->getAddressLine3() ?? '';

        // Map address lines
        $result = self::mapStreetAndCompany($result, $addressLine1, $addressLine2, $addressLine3, self::isPackstation($address));


        /*
         * Unmapped Amazon fields:
         *
         * $address->getCounty()
         * $address->getDistrict()
         *
         */

        return $result;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    protected static function mapStreetAndCompany($result, $addressLine1, $addressLine2, $addressLine3, $isPackstation = false) {
        $street = '';
        $company = '';
        $houseNumber = '';

        // heuristical approach as defined by Amazon
        if (!empty($addressLine3)) {
            // if the third address line is set, we interpret this line as street and number, and the first two lines as company part
            $street = $addressLine3;
            $company = $addressLine1 . ' ' . $addressLine2;
        } else {
            // no 3rd addressLine is set
            if (!empty($addressLine2)) {
                // Special weird case: In some German/Austrian addresses, address line 2 might contain the house number ("a string of max length 9, starting with a number").
                // In that case we expect line 1 to be the street, not the company, and concatenate it with addressline2 - it will be split later, again.
                if (($result->cLand === 'DE' || $result->cLand === 'AT') && preg_match('/^\d.{0,8}$/', $addressLine2)) {
                    $street = $addressLine1 . ' ' . $addressLine2;
                    $company = '';
                } else {
                    // if no 3rd line is set, but the 2nd line is set and the special case above does not apply, we interpret the second line as street and number, and the first line as company part
                    $street = $addressLine2;
                    $company = $addressLine1;
                }
            } else {
                // only the first line is set, we interpret it as street and number, and no company name
                $street = $addressLine1;
                $company = '';
            }
        }

        /**
         * Now, we have set the street, but not the house number, yet.
         * Try a heuristic extraction of house numbers from a street.
         * If this is not 100% accurate, it does not matter as the WAWI will put together street and housenumber again.
         * So in the worst case, we might end up with a street containing parts of the house number (ie: Street 3 A might result in "Street 3" and "A")
         */
        $split = mb_split('\s+', $street);
        if (!empty($split) && \count($split) > 1) {
            $houseNumber = array_pop($split);
            $street = implode(' ', $split);
        } else {
            // The street does not contain any spaces - lets try to grab a housenumber from it with a regex
            $matches = [];
            // Match against a string that starts with at least one non-digit character and match the street name (as "all the leading non-digit-characters")
            if (\preg_match('/^([^\d]+)(\d+.*)$/u', $street, $matches)) {
                if (!empty($matches) && \count($matches) === 3) {
                    $street = $matches[1];
                    $houseNumber = $matches[2];
                } else {
                    // no dice, just set the housenumber to empty - this is not desirable but will work, nonetheless.
                    $houseNumber = '';
                }
            } else {
                // no dice, just set the housenumber to empty - this is not desirable but will work, nonetheless.
                $houseNumber = '';
            }
        }

        // finally set the computed values on the result object and return it
        $result->cStrasse = $street;
        $result->cHausnummer = $houseNumber;

        /**
         * If the address is a packstation and the company field contains a purely numeric value with 5 or more digits, then the number in company
         * is probably the post ident number. For JTL-Shipping to work, it requires this number in the AdressZusatz-Field instead of the
         * company field.
         */
        if($isPackstation && \preg_match('/^\s*\d{5,}\s*$/u', $company) === 1) {
            $result->cAdressZusatz = $company;
        } else {
            $result->cFirma = $company;
        }
        return $result;
    }

    /**
     * Placeholder function - currently both JTL and Amazon use the same format (ISO-3166-2)
     * @param $countryCode
     * @return string
     */
    protected static function mapCountryCodeAmazonToJtl($countryCode): string {
        return $countryCode;
    }

    /**
     * Checks if an amazon address is a packstation address
     * @param Address $address
     * @return bool
     */
    public static function isPackstation(Address $address): bool {
        // check if Packstation was selected as delivery address.
        $addressString = $address->getAddressLine1() ?? '';
        $addressString .= $address->getAddressLine2() ?? '';
        $addressString .= $address->getAddressLine3() ?? '';
        return mb_stripos($addressString, 'packstation') !== false;
    }

    public static function overrideBillingAddressWithAmazonPayData(Rechnungsadresse $destination, Rechnungsadresse $source): Rechnungsadresse {

        $destination->cVorname = $source->cVorname;
        $destination->cNachname = $source->cNachname;
        $destination->cStrasse = $source->cStrasse;
        $destination->cHausnummer = $source->cHausnummer;

        $destination->cFirma = $source->cFirma;

        $destination->cOrt = $source->cOrt;
        $destination->cPLZ = $source->cPLZ;
        $destination->cTel = $source->cTel;

        $destination->cBundesland = $source->cBundesland;
        $destination->cLand = $source->cLand;
        $destination->angezeigtesLand  = $source->angezeigtesLand;
        // these value do never come from an Amazon Pay address:
        /*
         * We chose to not unset these - they might be useful and should not interfere with a differing address
         *
         * cUSTID
         * cAnrede
         * cAnredeLocalized
         * cTitel
         * cFax
         * cMobil
         * cMail (this is set another way)
         * cWWW
         */
        // These are overridden/reset because they might falsify the address
        $destination->cAdressZusatz = '';
        $destination->cZusatz = '';
        return $destination;
    }

    /**
     * @param mixed $jtlAddress (maybe Adresse or stdClass)
     * @return Address
     */
    public static function mapAddressJtlToAmazon($jtlAddress): Address {
        $addressArray = [
            'name' => $jtlAddress->cVorname . ' ' . $jtlAddress->cNachname,
            // Address Line 1 is company and 2 is street + no, if company is set, else line 1 is street+no, address line 3 is never set by us
            'addressLine1' => empty($jtlAddress->cFirma) ? ($jtlAddress->cStrasse . ' ' . $jtlAddress->cHausnummer) : $jtlAddress->cFirma,
            'addressLine2' => empty($jtlAddress->cFirma) ? null : ($jtlAddress->cStrasse . ' ' . $jtlAddress->cHausnummer),
            'city' => $jtlAddress->cOrt,
            'stateOrRegion' => empty($jtlAddress->cBundesland) ? null : $jtlAddress->cBundesland,
            'postalCode' => $jtlAddress->cPLZ,
            'countryCode' => $jtlAddress->cLand,
            'phoneNumber' => isset($jtlAddress->cTel) && $jtlAddress->cTel !== '' ? $jtlAddress->cTel : $jtlAddress->cMobil
        ];
        // Map html entities (which *may* be present) into UTF-8 characters
        $addressArray = array_map(static function($field) {
            return $field === null ? null : html_entity_decode($field, ENT_COMPAT, 'UTF-8');
        }, $addressArray);
        return new Address($addressArray);
    }

    /**
     * Returns a checksum for the given address that can be used to identify delivery address fraud.
     * @param Adresse $jtlAddress
     * @return string
     */
    public static function getAddressChecksum(Adresse $jtlAddress): string {
        $concatenatedAddress = ($jtlAddress->cStrasse ?? '') . ($jtlAddress->cHausnummer ?? '') . ($jtlAddress->cPLZ ?? '') . ($jtlAddress->cOrt ?? '') . ($jtlAddress->cLand ?? '');
        // JTL sometimes (?) encodes strings in the address with HTML Entities - for whatever reason - so we have to convert them back, just in case.
        // (Test case to reproduce: guest order with billing address with umlaut in city, no special shipping address, use APB for checkout)
        $concatenatedAddress = html_entity_decode($concatenatedAddress, ENT_NOQUOTES|ENT_SUBSTITUTE, 'UTF-8');
        return hash('md5', preg_replace("/[^a-z0-9]/i", "", $concatenatedAddress));
    }
}