<?php 

declare(strict_types=1);
namespace ViesParser;

class ViesParser
    {
    public function get_parsed_address($vat_number, $address) {

        $address = trim($address);
        $vat = trim($vat_number);
        $country_code = substr($vat, 0, 2);
        $newlines = substr_count($address, "\n" );

        //only attempt parsing for countries tested
        //DE does not return address on VIES at all
        //IE has pretty much unparsable addresses in VIES - split by commas, in different orders, without zip codes, often without street number etc
        //ES VIES does not return address unless you tell it what it is
        if (!in_array($country_code, ['SK', 'NL', 'BE', 'FR', 'PT', 'IT', 'FI'])) {
            return false;
        }

        if ($newlines == 1 and in_array($country_code, ['NL', 'BE', 'FR', 'FI']) ){ //Countries in expected format
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

        if ($newlines == 1 and in_array($country_code, ['IT']) ){
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

        if ($newlines == 2 and in_array($country_code, ['PT']) ){
            $address_split = explode("\n", $address);
            $street = $address_split[0];
            $city = $address_split[1];
            list($zip) = explode(" ", $address_split[2], 2);
            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }

        //in these cases the first line is "name of the place", not exactly street, but for ordering something to this address you put in in the street line
        if ($newlines == 2 and in_array($country_code, ['FR']) ){ 
            $address_split = explode("\n", $address);
            $street = $address_split[0] .', '. $address_split[1] ;
            list($zip, $city) = explode(" ", $address_split[2], 2);
            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }

        if ($newlines == 2 and in_array($country_code, ['SK']) ){ //Vetsina SK address
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


        if ($newlines == 1 and in_array($country_code, ['SK']) ){ // vetsinou ma tenhle format Bratislava
            $address_split = explode("\n", $address);
            $street = $address_split[0];
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
