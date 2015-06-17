<?php

use Mockery as Mockery;     // Remove this and tearDown() when next Lumen patch
                            // is released

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    use AssertionTrait;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }


    /**
     * In Lumen 5.1.0, there is a bug in Laravel\Lumen\Testing\TestCase where
     * Mockery::close() is called if Mockery exists, but the class has omitted
     * to reference Mockery outside the class namespace, so unit testing
     * becomes impossible when Mockery is available.
     *
     * This will most certainly be fixed in the next minor Lumen update, but
     * for now, we override the method and define the reference here, to keep
     * it working.
     *
     * When Lumen gets the next update, we can remove this.
     */
    public function tearDown()
    {
        if (class_exists('Mockery')) {
            Mockery::close();
        }

        if ($this->app) {
            foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
                call_user_func($callback);
            }

            $this->app->flush();

            $this->app = null;
        }
    }

}
