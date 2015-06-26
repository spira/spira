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

    public function testTestEntityValidator()
    {
        $validator = $this->app->make('App\Http\Validators\TestEntityValidator');

        $this->assertTrue(is_array($validator->rules()));
    }

    public function testIdOverwrite()
    {
        $this->setExpectedException('App\Exceptions\ValidationException');

        $validator = $this->app->make('App\Http\Validators\TestEntityValidator');

        $validator->with(['entity_id' => 'foo'])->id('foobar');
    }

    public function testIdOverwriteExceptionResponse() {
        try {
            $validator = $this->app->make('App\Http\Validators\TestEntityValidator');

            $validator->with(['entity_id' => 'foo'])->id('foobar');
        }

        catch (App\Exceptions\ValidationException $expected) {
            $object = json_decode($expected->getResponse()->getContent());

            $this->assertEquals('There was an issue with the validation of provided entity', $object->message);
            $this->assertObjectHasAttribute('entityId', $object->invalid);
            $this->assertEquals('mismatch_id', $object->invalid->entityId[0]->type);
            $this->assertEquals(422, $expected->getResponse()->getStatusCode());
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCamelCaseTransform() {
        try {
            $data = ['multi_word_column_title' => 0.12];

            $this->validator->with($data)->validate();
        }

        catch (App\Exceptions\ValidationException $expected) {
            $object = json_decode($expected->getResponse()->getContent());

            $this->assertObjectHasAttribute('multiWordColumnTitle', $object->invalid);
            $this->assertEquals(422, $expected->getResponse()->getStatusCode());
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}

class Validation extends App\Services\Validator
{
    protected function model()
    {
        return;
    }

    public function rules()
    {
        return [
            'float' => 'float',
            'uuid' => 'uuid',
            'multi_word_column_title' => 'string',
        ];
    }
}
