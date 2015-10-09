<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Services\ModelFactory;

trait ModelFactoryTrait
{
    /**
     * Making it static not to reinit for each TestCase.
     * @var ModelFactory
     */
    protected $modelFactory;

    public function bootModelFactoryTrait()
    {
        $this->modelFactory = $this->app->make(ModelFactory::class);
    }

    /**
     * @param string|null $factoryName
     * @param string|null $definedName
     * @return \App\Services\ModelFactoryInstance
     */
    public function getFactory($factoryName = null, $definedName = 'default')
    {
        return $this->modelFactory->get($factoryName, $definedName);
    }
}
