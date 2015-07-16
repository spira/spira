<?php namespace App\Services\Datasets;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;

class Countries
{
    /**
     * Guzzle Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Assign dependencies.
     *
     * @param  Client  $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function all()
    {
        $client = new $this->client;
        $response = $client->get('https://restcountries.eu/rest/v1/all');
        $countries = new Collection;

        foreach ($response->json() as $country) {
            $countries->push(new Collection([
                'country_name' => $country['name'],
                'country_code' => $country['alpha2Code']
            ]));
        }

        return $countries;
    }
}
