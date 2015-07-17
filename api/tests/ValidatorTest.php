<?php

use Rhumsaa\Uuid\Uuid;

class ValidatorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->validator = $this->app->make('validator');
    }

    public function testPassingFloatValidation()
    {
        $data = ['float' => 12.042];

        $this->assertTrue($this->validator->make($data,['float'=>'float'])->passes());
    }

    public function testFailingFloatValidation()
    {
        $data = ['float' => 'foo'];
        $validation = $this->validator->make($data,['float'=>'float']);
        $this->assertFalse($validation->passes());

        $this->assertStringEndsWith('must be a float.', $validation->messages()->get('float')[0]);
    }

    public function testPassingUuidValidation()
    {
        $data = ['uuid' => (string) Uuid::uuid4()];
        $this->assertTrue($this->validator->make($data,['uuid'=>'uuid'])->passes());
    }

    public function testFailingUuidValidation()
    {
        $data = ['uuid' => 'foobar'];
        $validation = $this->validator->make($data,['uuid'=>'uuid']);
        $this->assertFalse($validation->passes());
        $this->assertStringEndsWith('must be an UUID string.', $validation->messages()->get('uuid')[0]);
    }

    public function testTestEntityValidator()
    {
        $entity = factory(App\Models\TestEntity::class)->create();;

        $this->assertTrue(is_array($validator->rules()));
    }

    public function testIdOverwrite()
    {
        $this->setExpectedException('App\Exceptions\ValidationException');

        $validator = $this->app->make('App\Http\Validators\TestEntityValidator');

        $validator->with(['entity_id' => 'foo'])->id('foobar');
    }

    public function testIdOverwriteExceptionResponse()
    {
        try {
            $validator = $this->app->make('App\Http\Validators\TestEntityValidator');

            $validator->with(['entity_id' => 'foo'])->id('foobar');
        } catch (App\Exceptions\ValidationException $expected) {
            $messages = $expected->getResponse()['invalid']->toArray();

            $this->assertArrayHasKey('entityId', $messages);
            $this->assertEquals('mismatch_id', $messages['entityId'][0]['type']);
            $this->assertEquals(422, $expected->getStatusCode());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCamelCaseTransform()
    {
        try {
            $data = ['multi_word_column_title' => 0.12];

            $this->validator->with($data)->validate();
        } catch (App\Exceptions\ValidationException $expected) {
            $messages = $expected->getResponse()['invalid']->toArray();

            $this->assertArrayHasKey('multiWordColumnTitle', $messages);
            $this->assertEquals(422, $expected->getStatusCode());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testValidateMany()
    {
        $data = [['float' => 0.12], ['float' => 0.13], ['float' => 0.14]];

        $this->assertNull($this->validator->with($data)->validateMany());
    }

    public function testValidateManyFails()
    {
        try {
            $data = [['float' => 0.12], ['float' => 'foobar'], ['float' => 0.14]];

            $this->validator->with($data)->validateMany();
        } catch (App\Exceptions\ValidationException $expected) {
            $messages = $expected->getResponse()['invalid']->toArray();

            $this->assertTrue(is_array($messages));
            $this->assertNull($messages[0]);
            $this->assertTrue(is_array($messages[1]));
            $this->assertArrayHasKey('float', $messages[1]);
            $this->assertNull($messages[2]);

            return;
        }
        $this->fail('An expected exception has not been raised.');
    }
//    public function testCamelCaseTransform()
//    {
//        try {
//            $data = ['multi_word_column_title' => 0.12];
//
//            $this->validator->with($data)->validate();
//        }
//
//        catch (App\Exceptions\ValidationException $expected) {
//            $messages = $expected->getResponse()['invalid']->toArray();
//
//            $this->assertArrayHasKey('multiWordColumnTitle', $messages);
//            $this->assertEquals(422, $expected->getStatusCode());
//            return;
//        }
//
//        $this->fail('An expected exception has not been raised.');
//    }
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
