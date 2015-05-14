<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    /**
     * Default preparation for each test
     */
    public function setUp()
    {
        parent::setUp();

        $this->prepareForTests();
    }

    /**
     * Creates the application.
     *
     * @return Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $unitTesting = true;

        $environment = null;

        $getenv = getenv('HTTP_ENVIRONMENT');
        if (!empty($getenv)) {
            $environment = getenv('HTTP_ENVIRONMENT');
        }

        if (empty($environment)) {
            $environment = 'local';
        }

        $testEnvironment = $environment;

        return require __DIR__.'/../../bootstrap/start.php';
    }

    /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     */
    private function prepareForTests()
    {
        Mail::pretend(true);
    }

}
