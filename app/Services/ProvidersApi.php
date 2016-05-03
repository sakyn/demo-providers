<?php

namespace App\Services;


/**
 * Class ProvidersApi
 * @package App\Services
 * @throws ProvidersApiException
 */
class ProvidersApi
{

	const PROVIDERS_POINT = "http://dev-intelligence.atoto.cz/api/v1/provider";

	const CHECK_ADDRESS_POINT = "http://dev-intelligence.atoto.cz/api/v1/address-check";

	/** @var  JsonApiClient */
	public $client;

	/** @var  array $providers */
	protected $providers;


	/**
	 * @param \App\Services\JsonApiClient $client
	 */
	public function __construct(JsonApiClient $client)
	{
		$this->client = $client;
	}


	/**
	 * @param $address
	 * @return array
	 * @throws ProvidersApiException
	 */
	public function getProviders($address)
	{
		try {

			//Load all providers @todo: cache!!
			$providers = $this->loadProviders();

			if (!($providers && is_array($providers))) throw new ProvidersApiException("Failed fetch providers list");

			//Fetch providersId for address
			$resp = $this->client->request(self::CHECK_ADDRESS_POINT, "POST", ['json' => $address]);

			//Filter only delivering
			$deliver_ids = array_keys(array_filter((array)$resp->address));

			$address = [];
			foreach ($providers as $provider) {
				if ($provider->active === TRUE && in_array($provider->id, $deliver_ids)) $address[] = $provider;
			}

			return $address;

		} catch (ApiClientException $e) {

			throw new ProvidersApiException("Failed load providers ", null, $e);
		}
	}

	/**
	 * Fetch providers details
	 * @return array
	 */
	private function loadProviders()
	{
		return $this->client->request(self::PROVIDERS_POINT);
	}


}

/**
 * Class ProvidersApiException
 * @package App\Services
 */
class ProvidersApiException extends \LogicException
{

}