# vies-parser

This library aims to parse addresses returned by EU's VIES API (VAT Information Exchange System). Normally the API returns adresses as one string, instead of street, city, zip. This may be be a big issue for automation.

Supported countries are in get_supported_countries(), currently that is ['SK', 'NL', 'BE', 'FR', 'PT', 'IT', 'FI', 'RO', 'SI', 'AT', 'PL', 'HR', 'EL', 'DK', 'EE', 'CZ'].

I try to add more countries and/or tweak the parser as I get more data from a project in production.

Please note that for some countries (e.g. DE, IE) it is not possible to parse address at all, since the VIES API does not return it or is too inconsistent.


## Installation

To install the latest version use `composer require sunkaflek/vies-parser`.


## Usage


```php
<?php

use ViesParser\ViesParser;
require_once __DIR__ . '/vendor/autoload.php';

$parser = new ViesParser();

$address = "Havenlaan 2\n1080 Sint-Jans-Molenbeek";
$vat = 'BE0462920226';

$parsed_address = $parser->get_parsed_address($vat, $address);


if ($parsed_address) {
    var_dump($parsed_address);
} else {
    echo 'cant parse yet';
}

```

## Notes

Adresses from VIES API can be obtained for example using the excelent library https://github.com/DragonBe/vies
