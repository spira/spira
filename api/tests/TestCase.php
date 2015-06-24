<?php

/**
 * @todo  Remove this when Lumen has a patch released to fix Mockery in Laravel\Lumen\Testing\TestCase
 * @link (Fixed here, will be in next release, https://github.com/laravel/lumen-framework/commit/38ac45d0e370a6249b38e4a8dc5642fdc0b18665)
 */
use Mockery as Mockery;     // Remove this and tearDown() when next Lumen patch
                            // is released

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    use AssertionTrait;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        $this->bootTraits();

        parent::setUp();
    }

    /**
     * Allow traits to have custom initialization built in.
     *
     * @return void
     */
    protected function bootTraits()
    {
        foreach (class_uses($this) as $trait) {
            if (method_exists($this, 'boot'.$trait)) {
                $this->{'boot'.$trait}();
            }
        }
    }

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
     *
     * @todo  Remove this when Lumen has a patch released to fix Mockery in Laravel\Lumen\Testing\TestCase
     * @link (Fixed here, will be in next release, https://github.com/laravel/lumen-framework/commit/38ac45d0e370a6249b38e4a8dc5642fdc0b18665)
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
