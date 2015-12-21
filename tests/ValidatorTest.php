<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests;

use Illuminate\Validation\Factory;
use Rhumsaa\Uuid\Uuid;
use Spira\Core\Model\Test\SecondTestEntity;
use Spira\Core\Model\Test\TestEntity;
use Spira\Core\Validation\SpiraValidator;

class ValidatorTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $validator;

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

    /**
     * @dataProvider dataExistsMorphedValidation
     */
    public function testExistsMorphedValidation($rule, $passes = true)
    {
        $item = $this->getFactory(TestEntity::class)->customize(['integer' => 123])->create();
        $validator = $this->validator->make(
            ['item_id' => $item->entity_id, 'item_type' => TestEntity::class],
            ['item_id' => $rule]
        );

        $this->assertEquals($passes, $validator->passes());

        if (! $passes) {
            $this->assertEquals('The item id must exists in corresponding table', $validator->messages()->get('item_id')[0]);
        }
    }

    public function dataExistsMorphedValidation()
    {
        return [
            ['exists_morphed:item_type', true],
            ['exists_morphed:item_type,entity_id', true],
            ['exists_morphed:item_type,hash', false],
            ['exists_morphed:item_type,,integer,123', true],
            ['exists_morphed:item_type,entity_id,integer,321', false],
            ['exists_morphed:'.TestEntity::class, true],
            ['exists_morphed:'.SecondTestEntity::class, false],
        ];
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

    public function testValidateUniqueWith()
    {
        $testEntity = $this->getFactory(TestEntity::class)->customize([
            'integer' => 123,
            'text' => 'foobar',
        ])->create();

        $testEntity->entity_id = (string) Uuid::uuid4();

        $validation = $this->validator->make($testEntity->toArray(), [
            'integer' => 'unique_with:'.TestEntity::getTableName().',text',
        ]);

        $this->assertFalse($validation->passes());
    }
}
