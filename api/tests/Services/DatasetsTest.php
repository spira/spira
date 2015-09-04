<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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

        /** @var Illuminate\Support\Collection $countries */
        $countries = $set->getDataset();
        $country = $countries->first();

        $this->assertInstanceOf('Illuminate\Support\Collection', $countries);
        $this->assertArrayHasKey('country_name', $country);
        $this->assertArrayHasKey('country_code', $country);
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
