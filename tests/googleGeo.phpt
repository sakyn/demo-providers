<?php


/**
 * TEST: Google Geocoding API.
 */


use App\Services\JsonApiClient;
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';       # při instalaci Composerem

require __DIR__ . '/../app/Services/JsonApiClient.php';
require __DIR__ . '/../app/Services/GoogleGeo/GoogleGeocoding.php';
require __DIR__ . '/../app/Services/GoogleGeo/GoogleGeoException.php';
require __DIR__ . '/../app/Services/ProvidersApi.php';


Tester\Environment::setup();

$c = new JsonApiClient(new GuzzleHttp\Client());
$o = new GoogleGeo\GoogleGeocoding("https://maps.google.com/maps/api/geocode/json", $c);


/**
 * Test array response
 */
Assert::type('array', $o->getAddress('Evaldova 10, Šumperk'));


/**
 * Test array length
 */
Assert::count(5, $o->getAddress('Jindřišká 14, Praha'));

/**
 * Non-existing address
 */
Assert::False($o->getAddress('Lorem Ipsum'));

/**
 * Exception on empty address
 */
Assert::exception(function () use ($o) {         # Očekáváme vyjímku
	$o->getAddress('');
}, '\GoogleGeo\InvalidArgumentException');