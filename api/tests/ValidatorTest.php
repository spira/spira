<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Services\SpiraValidator;
use Rhumsaa\Uuid\Uuid;

class ValidatorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->validator = $this->app->make('validator');
    }

    public function testSpiraValidator()
    {
        $data = ['decimal' => 'foo'];
        $validation = $this->validator->make($data, ['decimal' => 'decimal']);
        $this->assertInstanceOf(SpiraValidator::class, $validation);
    }

    public function testPassingDecimalValidation()
    {
        $data = ['decimal' => 12.042];
        $this->assertTrue($this->validator->make($data, ['decimal' => 'decimal'])->passes());

        $data = ['decimal' => 12];
        $this->assertTrue($this->validator->make($data, ['decimal' => 'decimal'])->passes());
    }

    public function testFailingDecimalValidation()
    {
        $data = ['decimal' => 'foo'];
        $validation = $this->validator->make($data, ['decimal' => 'decimal']);
        $this->assertFalse($validation->passes());

        $this->assertStringEndsWith('must be a decimal.', $validation->messages()->get('decimal')[0]);
    }

    public function testPassingUuidValidation()
    {
        $data = ['uuid' => (string) Uuid::uuid4()];
        $this->assertTrue($this->validator->make($data, ['uuid' => 'uuid'])->passes());
    }

    public function testFailingUuidValidation()
    {
        $data = ['uuid' => 'foobar'];
        $validation = $this->validator->make($data, ['uuid' => 'uuid']);
        $this->assertFalse($validation->passes());
        $this->assertStringEndsWith('must be an UUID string.', $validation->messages()->get('uuid')[0]);
    }

    public function testPassingCountryValidation()
    {
        $data = ['country' => 'SE'];

        $validation = $this->validator->make($data, ['country' => 'country']);
        $this->assertTrue($validation->passes());
    }

    public function testFailingCountryValidation()
    {
        $data = ['country' => 'SWE'];

        $validation = $this->validator->make($data, ['country' => 'country']);
        $this->assertFalse($validation->passes());

        $this->assertStringEndsWith('valid country code.', $validation->messages()->get('country')[0]);
    }

    public function testPassingValidateAlphaDashSpace()
    {
        $data = ['username' => 'foo1 bar_-.baz'];

        $validation = $this->validator->make($data, ['username' => 'alpha_dash_space']);
        $this->assertTrue($validation->passes());
    }

    public function testFailingValidateAlphaDashSpace()
    {
        $data = ['username' => '#foo'];
        $validation = $this->validator->make($data, ['username' => 'alpha_dash_space']);
        $this->assertFalse($validation->passes());

        $data = ['username' => 'foo,bar'];
        $validation = $this->validator->make($data, ['username' => 'alpha_dash_space']);
        $this->assertFalse($validation->passes());

        $data = ['username' => '$foo'];
        $validation = $this->validator->make($data, ['username' => 'alpha_dash_space']);
        $this->assertFalse($validation->passes());

        $this->assertContains('spaces', $validation->messages()->get('username')[0]);
    }
}
