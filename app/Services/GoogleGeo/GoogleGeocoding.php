<?php

namespace GoogleGeo;

use App\Services\ApiClientException;
use App\Services\JsonApiClient;

/**
 * Class GoogleGeocoding
 * @package GoogleGeo
 */
class GoogleGeocoding
{


	const VALID_PRECISION = "ROOFTOP";

	/** @var JsonApiClient */
	public $client;

	/** @var  $geopoint / Google API endpoint */
	protected $geoPoint;


	/**
	 * @param $geoPoint
	 * @param JsonApiClient $client
	 */
	public function __construct($geoPoint, JsonApiClient $client)
	{

		$this->geoPoint = $geoPoint;
		$this->client = $client;

	}


	/**
	 * @param $address
	 * @return array|bool
	 * @throws GoogleGeoException
	 * @throws InvalidArgumentException
	 */
	public function getAddress($address)
	{

		// Address is required
		if (!$address) throw new InvalidArgumentException("Address is required");

		return $this->fetchAddress($address);


	}


	/**
	 * @param $query
	 * @return array|null
	 * @throws InvalidArgumentException
	 */
	protected function fetchAddress($query)
	{

		try {

			$resp = $this->client->request($this->geoPoint, "GET", ['query' => ['address' => $query]]);

			return isset($resp->results[0]) ? $this->parseResponse($resp->results[0]) : null;

		} catch (ApiClientException $e) {
			//Log $e->getMessage()
			throw new GoogleGeoException("Failed verify address ", null, $e);
		}

	}


	/**
	 * @param $result
	 * @return array|bool
	 */
	protected function parseResponse($result)
	{

		//Check required params & location precission
		if (!isset($result->address_components) || $result->geometry->location_type !== self::VALID_PRECISION) return false;

		$response = [];
		foreach ($result->address_components as $component) {
			switch ($component->types) {
				case in_array('street_number', $component->types):
					$response['street_number'] = $component->long_name;
					break;
				case in_array('route', $component->types):
					$response['route'] = $component->long_name;
					break;
				case in_array('postal_town', $component->types):
					$response['postal_town'] = $component->long_name;
					break;
				case in_array('postal_code', $component->types):
					$response['postal_code'] = $component->long_name;
					break;
				case in_array('country', $component->types):
					$response['country'] = $component->long_name;
					break;
			}

		}

		//If VALID_PRECISION < ROOFTOP check array keys
		return $response;
	}


}

