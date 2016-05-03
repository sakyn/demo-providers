<?php


/**
 * TEST: Atoto providers API.
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
$o = new \App\Services\ProvidersApi($c);


/**
 * Test array response
 */
Assert::type('array', $o->getProviders([
	"street_number" => 8,
	"route" => "Minská",
	"postal_town" => "Praha",
	"postal_code" => "101 00",
	"country" => "Česká republika"
]));

/**
 * Test array response
 */
Assert::count(5, $o->getProviders([
	"street_number" => 8,
	"route" => "Minská",
	"postal_town" => "Praha",
	"postal_code" => "101 00",
	"country" => "Česká republika"
]));


/**
 * Test array length
 */
Assert::count(0, $o->getProviders([
	"street_number" => 10,
	"route" => "Evaldova",
	"postal_town" => "Šumperk",
	"postal_code" => "787 01",
	"country" => "Česká republika"
]));


