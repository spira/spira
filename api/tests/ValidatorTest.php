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

    public function testRules()
    {
        $this->assertTrue(is_array($this->validator->rules()));
    }

    public function testPassingFloatValidation()
    {
        $data = ['float' => 12.042];

        $this->assertTrue($this->validator->with($data)->passes());
    }

    public function testFailingFloatValidation()
    {
        $data = ['float' => 'foo'];

        $this->assertFalse($this->validator->with($data)->passes());

        $this->assertStringEndsWith('must be a float.', $this->validator->errors()->get('float')[0]);
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

        $this->assertStringEndsWith('must be an UUID string.', $this->validator->errors()->get('uuid')[0]);
    }
}

class Validation extends App\Services\Validator
{
    public function rules()
    {
        return [
            'float' => 'float',
            'uuid' => 'uuid'
        ];
    }
}
