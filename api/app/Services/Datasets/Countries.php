<?php namespace App\Services\Datasets;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Exceptions\ServiceUnavailableException;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class Countries extends Dataset
{
    /**
     * Guzzle client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * API endpoint to retrieve countries.
     *
     * @var string
     */
    protected $endpoint = 'https://restcountries.eu/rest/v1/all';

    /**
     * Assign dependencies.
     *
     * @param  Client          $client
     * @param CacheRepository  $cache
     * @return void
     */
    public function __construct(Client $client, CacheRepository $cache)
    {
        $this->client = $client;

        parent::__construct($cache);
    }

    /**
     * Get the dataset.
     *
     * @return Collection
     */
    protected function getDataset()
    {
        try {
            $client = new $this->client;
            $response = $client->get($this->getEndpoint());
        } catch (ClientException $e) {
            throw new ServiceUnavailableException;
        }

        $countries = new Collection;

        foreach ($response->json() as $country) {
            $countries->push(new Collection([
                'country_name' => $country['name'],
                'country_code' => $country['alpha2Code']
            ]));
        }

        return $countries;
    }

    /**
     * Get API endpoint.
     *
     * @return  string
     */
    protected function getEndpoint()
    {
        return $this->endpoint;
    }
}
