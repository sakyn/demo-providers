<?php

namespace App\Services;

use GuzzleHttp;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class JsonApiClient
 * @package App\Services
 */
class JsonApiClient
{


	/** @var GuzzleHttp\Client */
	public $client;


	/**
	 * @param GuzzleHttp\Client $client
	 */
	public function __construct(GuzzleHttp\Client $client)
	{

		$this->client = $client;

	}


	/**
	 * Create request and fetch JSON
	 *
	 * @param $url
	 * @param string $method
	 * @param null $query
	 * @return mixed
	 */
	public function request($url, $method = "GET", $query = null)
	{

		try {

			$response = $this->client->request($method, $url, $query ? $query : []);

			//Bad response
			if ($response->getStatusCode() !== 200) {
				throw new ApiClientException("Bad status HTTP/" . $response->getStatusCode() . " " . $response->getReasonPhrase());
			}

			return $this->processResponse($response);

		} catch (GuzzleHttp\Exception\ConnectException $e) {
			throw new ApiClientException("Failed connect to API");

		} catch (GuzzleHttp\Exception\ClientException $e) {
			throw new ApiClientException("Client exception: " . $e->getMessage());
		}
	}


	/**
	 * @param GuzzleHttp\Psr7\Response $res
	 * @return mixed
	 */
	private function processResponse(GuzzleHttp\Psr7\Response $res)
	{

		try {

			return Json::decode($res->getBody());

		} catch (JsonException $e) {
			throw new ApiClientException("Bad response (invalid JSON)");
		}


	}


}


/**
 * Class ApiClientException
 * @package App\Services
 */
class ApiClientException extends \LogicException
{

}