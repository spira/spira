<?php

use Mockery as m;
use Rhumsaa\Uuid\Uuid;

class ValidatorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->validator = $this->app->make('Validation');
    }

    public function testPassingUuidValidation()
    {
        $data = ['uuid' => (string) Uuid::uuid4()];

        $this->assertTrue($this->validator->with($data)->passes());
    }

    public function testFailingUuidValidation()
    {
        $data = ['uuid' => 'foobar'];

        $this->assertFalse($this->validator->with($data)->passes());
    }
}

class Validation extends App\Services\Validator
{
    public function rules()
    {
        return ['uuid' => 'uuid'];
    }
}
