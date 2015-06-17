<?php

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    use AssertionTrait, CrawlerTrait;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
