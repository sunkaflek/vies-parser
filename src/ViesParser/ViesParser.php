<?php 

declare(strict_types=1);
namespace ViesParser;


class ViesParser {

    //Returns currently supported countries. Not all countries return all data, see RO for example
    public function get_supported_countries() {
        return ['SK', 'NL', 'BE', 'FR', 'PT', 'IT', 'FI', 'RO', 'SI', 'AT', 'PL', 'HR', 'EL', 'DK', 'EE', 'CZ'];
    }


    public function get_parsed_address($vat_number, $address) {

        $address = trim($address);
        $vat = trim($vat_number);
        $country_code = substr($vat, 0, 2);
        $newlines = substr_count($address, "\n" );

        /*
        Only attempt parsing for countries tested, the rest returns false
        
        -DE does not return address on VIES at all
        -IE has pretty much unparsable addresses in VIES - split by commas, in different orders, without zip codes, often without street number etc
        -ES VIES does not return address unless you tell it what it is
        -RO does not have ZIP codes in VIES data, but we parse the rest. ZIP will return false - needs to be input by customer manualy
        -EL additionaly gets transliterated to English characters (resulting in Greeklish)

        */
        if (!in_array($country_code, $this-> get_supported_countries())) {
            return false;
        }

        if ($newlines == 1 and in_array($country_code, ['NL', 'BE', 'FR', 'FI', 'AT', 'PL', 'DK']) ){ //Countries in expected format
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

        //Slovenia has everything on one line, split by comma, but seems fairly regular
        if ($newlines == 0 and in_array($country_code, ['SI', 'HR']) ){
            $address_split = explode(",", $address);
            $street = $address_split[0];
            if (count($address_split) == 3) $street = $street . ', ' . trim($address_split[1]); //sometimes they have aditional thing after street, seems to be city, but better not to omit
            list($zip, $city) = explode(" ", trim($address_split[array_key_last($address_split)]), 2);
            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }


        if ($newlines == 0 and in_array($country_code, ['EL']) ){
            $address = $this->make_greeklish($address);
            $hyphen_pos = strpos($address, ' - ');
            $city = substr($address, $hyphen_pos+3);
            $address_without_city = substr($address, 0, $hyphen_pos);
            $zip_pos = strrpos($address_without_city, ' ');
            $zip = substr($address_without_city, $zip_pos+1);
            $address_without_zip_and_city = substr($address_without_city, 0, $zip_pos);
            $street = trim($address_without_zip_and_city);

            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }

        //Romania does not have ZIP codes in VIES data
        if ($newlines == 1 and in_array($country_code, ['RO']) ){
            $address_split = explode("\n", $address);
            $street = trim($address_split[1]);
            $city = trim($address_split[0]);
            return [
                'address' => $address,
                'street' => $street,
                'zip' => false,
                'city' => $city,
                'country_code' => $country_code
            ];
        }

        //Romania does not have ZIP codes in VIES data
        //With 3 lines, it has apartement in the last line - we put it on the start of street line
        if ($newlines == 2 and in_array($country_code, ['RO']) ){
            $address_split = explode("\n", $address);
            $street = trim($address_split[2]) .', '. trim($address_split[1]);
            $city = trim($address_split[0]);
            return [
                'address' => $address,
                'street' => $street,
                'zip' => false,
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

        if ($newlines == 0 and in_array($country_code, ['EE']) and strpos($address, "  ") !== false){
            $address_split = explode("  ", $address);
            foreach ($address_split as $key => $value) { //sometimes they have more than 2 space as divider, we trim the additional ones here
                $address_split[$key] = trim($address_split[$key]);
            }
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

        if ($newlines == 1 and in_array($country_code, ['CZ']) ){ //Countries in expected format
            $address_split = explode("\n", $address);
            $street = $address_split[0];
            $pos = strpos($address_split[1], ' ', strpos($address_split[1], ' ') + 1); //second space marks ending of ZIP code
            if ($pos === false) return false;
            list($zip, $city) = [substr($address_split[1], 0, $pos), substr($address_split[1], $pos)];
            return [
                'address' => $address,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country_code' => $country_code
            ];
        }

        if ($newlines == 2 and in_array($country_code, ['CZ']) ){ //Countries in expected format
            $address_split = explode("\n", $address);
            $street = $address_split[0] . ', '. $address_split[1];
            $pos = strpos($address_split[2], ' ', strpos($address_split[2], ' ') + 1); //second space marks ending of ZIP code
            if ($pos === false) return false;
            list($zip, $city) = [substr($address_split[2], 0, $pos), substr($address_split[2], $pos)];
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

    //https://gist.github.com/teomaragakis/7580134
    //transliterates Greek characters to English
    private function make_greeklish($text) {
        $expressions = array(
           '/[αΑ][ιίΙΊ]/u' => 'e',
           '/[οΟΕε][ιίΙΊ]/u' => 'i',
           '/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
           '/[αΑ][υύΥΎ]/u' => 'av',
           '/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
           '/[εΕ][υύΥΎ]/u' => 'ev',
           '/[οΟ][υύΥΎ]/u' => 'ou',
           '/(^|\s)[μΜ][πΠ]/u' => '$1b',
           '/[μΜ][πΠ](\s|$)/u' => 'b$1',
           '/[μΜ][πΠ]/u' => 'mp',
           '/[νΝ][τΤ]/u' => 'nt',
           '/[τΤ][σΣ]/u' => 'ts',
           '/[τΤ][ζΖ]/u' => 'tz',
           '/[γΓ][γΓ]/u' => 'ng',
           '/[γΓ][κΚ]/u' => 'gk',
           '/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'if$1',
           '/[ηΗ][υΥ]/u' => 'iu',
           '/[θΘ]/u' => 'th',
           '/[χΧ]/u' => 'ch',
           '/[ψΨ]/u' => 'ps',
           '/[αά]/u' => 'a',
           '/[βΒ]/u' => 'v',
           '/[γΓ]/u' => 'g',
           '/[δΔ]/u' => 'd',
           '/[εέΕΈ]/u' => 'e',
           '/[ζΖ]/u' => 'z',
           '/[ηήΗΉ]/u' => 'i',
           '/[ιίϊΙΊΪ]/u' => 'i',
           '/[κΚ]/u' => 'k',
           '/[λΛ]/u' => 'l',
           '/[μΜ]/u' => 'm',
           '/[νΝ]/u' => 'n',
           '/[ξΞ]/u' => 'x',
           '/[οόΟΌ]/u' => 'o',
           '/[πΠ]/u' => 'p',
           '/[ρΡ]/u' => 'r',
           '/[σςΣ]/u' => 's',
           '/[τΤ]/u' => 't',
           '/[υύϋΥΎΫ]/u' => 'i',
           '/[φΦ]/iu' => 'f',
           '/[ωώ]/iu' => 'o',
           '/[Α]/iu' => 'a', //added as otherwise "A" kept as capitals
        );

       $text = preg_replace( array_keys($expressions), array_values($expressions), $text);
       return $text;
    }

}
