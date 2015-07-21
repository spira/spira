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

        $this->assertTrue($this->validator->make($data, ['float'=>'float'])->passes());
    }

    public function testFailingFloatValidation()
    {
        $data = ['float' => 'foo'];
        $validation = $this->validator->make($data, ['float'=>'float']);
        $this->assertFalse($validation->passes());

        $this->assertStringEndsWith('must be a float.', $validation->messages()->get('float')[0]);
    }

    public function testPassingUuidValidation()
    {
        $data = ['uuid' => (string) Uuid::uuid4()];
        $this->assertTrue($this->validator->make($data, ['uuid'=>'uuid'])->passes());
    }

    public function testFailingUuidValidation()
    {
        $data = ['uuid' => 'foobar'];
        $validation = $this->validator->make($data, ['uuid'=>'uuid']);
        $this->assertFalse($validation->passes());
        $this->assertStringEndsWith('must be an UUID string.', $validation->messages()->get('uuid')[0]);
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

    // public function testPassingCountryValidation()
    // {
    //     $data = ['country' => 'SE'];

    //     $this->assertTrue($this->validator->with($data)->passes());
    // }

    // public function testFailingCountryValidation()
    // {
    //     $data = ['country' => 'SWE'];

    //     $this->assertFalse($this->validator->with($data)->passes());

    //     $this->assertStringEndsWith('valid country code.', $this->validator->errors()->get('country')[0]);
    // }
}
