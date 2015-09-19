<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
use Illuminate\Support\Debug\Dumper;
use Laravel\Lumen\Testing\AssertionsTrait as BaseAssertionsTrait;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;


/**
 * Additional assertions not offered by Lumen's TestCase or PHPUnit.
 */
trait AssertionsTrait
{

    use BaseAssertionsTrait {
        BaseAssertionsTrait::assertResponseStatus as baseAssertResponseStatus;
    }
    /**
     * Assert the response is a JSON array.
     *
     * @return $this
     */
    public function assertJsonArray()
    {
        $array = json_decode($this->response->getContent(), true);

        $this->assertTrue(is_array($array));

        return $this;
    }

    /**
     * Assert the response is a JSON array with multiple entries.
     *
     * @return $this
     */
    public function assertJsonMultipleEntries()
    {
        $array = json_decode($this->response->getContent(), true);

        $this->assertTrue(count($array) > 1);

        return $this;
    }

    /**
     * Assert that the client response has no content.
     *
     * @return void
     */
    public function assertResponseHasNoContent()
    {
        $actual = $this->response->getContent();

        return $this->assertEmpty($this->response->getContent(), "Expected no content, got {$actual}.");
    }

    /**
     * Assert the date is a valid ISO 8601 date.
     *
     * @param string $date
     *
     * @return $this
     */
    public function assertValidIso8601Date($date)
    {
        $this->assertTrue($this->checkValidIso8601Date($date), 'Valid ISO8601 date');

        return $this;
    }

    /**
     * Validate a string that is is a valid ISO 8601 date.
     *
     * @param string $dateStr
     *
     * @return bool
     */
    protected function checkValidIso8601Date($dateStr)
    {

        //regex via http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
        $iso8601Regex = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
        if (preg_match($iso8601Regex, $dateStr)) {
            try {
                new \Carbon\Carbon($dateStr);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    public function assertException($message, $statusCode, $exception)
    {
        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus($statusCode);
        $this->assertContains($message, $body->message);
        $this->assertContains($exception, $body->debug->exception);
    }

    /**
     * Assert status code, and on failure print the output to assist debugging.
     * @param int $code
     */
    public function assertResponseStatus($code)
    {
        try {
            $this->baseAssertResponseStatus($code);
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $content = $this->response->getContent();

            $json = json_decode($content);

            //check to see if the response was valid json, if so assign the object to $content
            if (json_last_error() === JSON_ERROR_NONE) {
                $content = $json;
            }

            $originalDefaultOutput = CliDumper::$defaultOutput;

            CliDumper::$defaultOutput = 'php://output';
            $dumper = new CliDumper();
            $dumper->dump((new VarCloner)->cloneVar($content));

            CliDumper::$defaultOutput = $originalDefaultOutput;

            throw $e;
        }
    }
}
