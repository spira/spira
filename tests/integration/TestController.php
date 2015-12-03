<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests\integration;

use Spira\Core\Controllers\EntityController;
use Spira\Core\Controllers\LocalizableTrait;
use Spira\Core\Model\Test\TestEntity;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class TestController extends EntityController
{
    use LocalizableTrait;

    protected $permissionsEnabled = false;

    protected $defaultRole = 'user';

    public function __construct(TestEntity $model, EloquentModelTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }

    public function urlEncode($id)
    {
        return $this->getResponse()->item(['test' => $id]);
    }

    /**
     * Test a standard internal exception.
     */
    public function internalException()
    {
        throw new \RuntimeException('Something went wrong');
    }

    /**
     * Test a fatal exception (has to be tested with guzzle to stop phpunit halting).
     *
     * @codeCoverageIgnore
     */
    public function fatalError()
    {
        call_to_non_existent_function();
    }
}
