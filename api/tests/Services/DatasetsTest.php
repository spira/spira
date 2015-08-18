<?php

use App\Exceptions\ServiceUnavailableException;

class DatasetsTest extends TestCase
{
    public function testCountries()
    {
        $client = new GuzzleHttp\Client;
        $cache = Mockery::mock('Illuminate\Contracts\Cache\Repository');

        $set = Mockery::mock('App\Services\Datasets\Countries', [$client, $cache])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $countries = $set->getDataset();
        $country = $countries->first();

        $this->assertInstanceOf('Illuminate\Support\Collection', $countries);
        $this->assertTrue($country->has('country_name'));
        $this->assertTrue($country->has('country_code'));
        $this->assertGreaterThan(1, $countries->count());
    }

    public function testCountriesServiceUnavailable()
    {
        $this->setExpectedExceptionRegExp(
            ServiceUnavailableException::class,
            '/unavailable/i',
            0
        );

        $client = new GuzzleHttp\Client;
        $cache = Mockery::mock('Illuminate\Contracts\Cache\Repository');

        $set = Mockery::mock('App\Services\Datasets\Countries', [$client, $cache])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $set->shouldReceive('getEndpoint')->once()->andReturn('https://restcountries.eu/foobar');

        $countries = $set->getDataset();
    }
}
