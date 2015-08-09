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

    public function testCamelCaseTransform()
    {
        $this->markTestSkipped('can be tested in testEntity');
        $data = ['multi_word_column_title' => 0.12];

        $validation = $this->validator->make($data, ['multi_word_column_title'=>'required|boolean']);
        $this->assertFalse($validation->passes());
        $validation->passes();
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
