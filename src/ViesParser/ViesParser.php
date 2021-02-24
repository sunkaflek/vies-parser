<?php declare(strict_types=1);

namespace ViesParser;

class ViesParser
    {
    public function get_parsed_address($vat_number, $address) {

        $address = trim($address);
        $vat = trim($vat_number);
        $country_code = substr($vat, 0, 2);
        $newlines = substr_count($address, "\n" );

        //only attempt parsing for countries tested
        if (!in_array($country_code, ['SK','NL','BE'])) {
            return false;
        }

        if ($newlines == 1 and in_array($country_code,  ['NL', 'BE']) ){ //Countries in expected format
            $address_split = explode("\n", $address);
            $street = $address_split[0];
            list($zip, $city) = explode(" ", $address_split[1], 2);
            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }

        if ($newlines == 2 and in_array($country_code,  ['SK']) ){ //Vetsina SK address
            $address_split = explode("\n", $address);
            $street = $address_split[0];
            list($zip, $city) = explode(" ", $address_split[1], 2);
            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }


        if ($newlines == 1 and in_array($country_code,  ['SK']) ){ // vetsinou ma tenhle format Bratislava
            $address_split = explode("\n", $address);
            $street = $address_split[0];
            d($address);
            d($vat_number);
            if ($address_split[1] === 'Slovensko') {
                list($zip, $city) = explode(" ", $address_split[0], 2);
                $street = ''; //v techto pripadech nemame ulici a cislo popisne, tj. nesmime prepisovat
            } else {
                list($zip, $city) = explode(" ", $address_split[1], 2);
            }
            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }

        return false;

    }

}
