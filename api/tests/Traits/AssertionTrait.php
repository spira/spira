<?php

/**
 * Additional assertions not offered by Lumen's TestCase or PHPUnit.
 */
trait AssertionTrait
{
    /**
     * Assert the response is a JSON array.
     *
     * @return $this
     */
    public function assertJsonArray()
    {
        $array =json_decode($this->response->getContent(), true);

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
        $array =json_decode($this->response->getContent(), true);

        $this->assertTrue(count($array) > 1);

        return $this;
    }

    /**
     * Assert the date is a valid ISO 8601 date.
     *
     * @param  string $date
     * @return $this
     */
    public function assertValidIso8601Date($date)
    {
        $this->assertTrue($this->checkValidIso8601Date($date), 'Valid ISO 8601 date');

        return $this;
    }

    /**
     * Validate a string that is is a valid ISO 8601 date.
     *
     * @param  string  $date
     * @return bool
     */
    public function checkValidIso8601Date($date)
    {
        // 2007-03-25T00:00:00+0000
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})\+(\d{4})$/', $date, $parts) == true) {
            $time = gmmktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);

            $input_time = strtotime($date);
            if ($input_time === false) return false;

            return $input_time == $time;
        } else {
            return false;
        }
    }
}
