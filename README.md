# vies-parser (WIP!)

This component aims to parse addresses returned by VIES API (VAT Information Exchange System). Normally the API returns adresses as one string, instead of street, city, zip. This may be be a big issue for automation.

Currently this library is able to parse only SK, BE and NL addresses, I hope to add more as I get more address data from another project.


## Installation

To install the latest stable version use `composer require sunkaflek/vies-parser`.


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
