<?php

namespace Spira\Responder\Paginator;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RangeRequest implements PaginatedRequestDecoratorInterface
{
    private $parsed = false;

    private $offset = null;

    private $limit = null;

    private $isGetLast = false;

    protected $rangeKey = 'entities';

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param null|int $default
     * @return mixed
     */
    public function getOffset($default = null)
    {
        $this->parse();

        return is_null($this->offset) ? $default : $this->offset;
    }

    /**
     * @param null|int $default
     * @param null|int $max
     * @return mixed
     */
    public function getLimit($default = null, $max = null)
    {
        $this->parse();
        $limit = is_null($this->limit) ? $default : $this->limit;

        return ($limit > $max) ? $max : $limit;
    }

    /**
     * states that limit should be applied to the end of the result set.
     * @return bool
     */
    public function isGetLast()
    {
        $this->parse();

        return $this->isGetLast;
    }

    /**
     * @return bool
     */
    private function parse()
    {
        if ($this->parsed) {
            return true;
        }

        $range = $this->getRequestedRange();

        //parsing the template \d-\d (ex. 20-39)
        $ranges = explode('-', $range);
        //if we have part before dash, we can find offset
        if (isset($ranges[0]) && $ranges[0] !== '') {
            $this->offset = $ranges[0];
        }

        //if we have part after dash, we can find limit
        if (isset($ranges[1]) && $ranges[1] !== '') {
            if (is_null($this->offset)) {
                // if we don't have offset, that means we want to get last-n entities
                $this->isGetLast = true;
                $this->limit = $ranges[1];
            } else {
                //if we have offset we calculate limit
                $this->limit = $ranges[1] - $ranges[0] + 1;
            }
        }

        if (! is_null($this->limit) && $this->limit <= 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid Range header');
        }

        return $this->parsed = true;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get requested range.
     * @return mixed
     */
    protected function getRequestedRange()
    {
        if ($this->getRequest()->headers) {
            $range = $this->getRequest()->headers->get('Range');

            if (strpos($range, $this->rangeKey.'=') !== 0) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid Range header, Expected format example - `Range: '.$this->rangeKey.'=0-100`');
            } else {
                return str_replace($this->rangeKey.'=', '', $range);
            }
        }

        throw new HttpException(Response::HTTP_BAD_REQUEST, 'Range header expected');
    }
}
