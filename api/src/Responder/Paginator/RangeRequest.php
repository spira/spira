<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 30.07.15
 * Time: 14:58
 */

namespace Spira\Responder\Paginator;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RangeRequest implements PaginatedRequestDecoratorInterface
{
    private $parsed = false;

    private $offset = null;

    private $limit = null;

    private $isGetLast = false;

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
        return is_null($this->offset)?$default:$this->offset;
    }

    /**
     * @param null|int $default
     * @param null|int $max
     * @return mixed
     */
    public function getLimit($default = null, $max = null)
    {
        $this->parse();
        $limit = is_null($this->limit)?$default:$this->limit;
        return ($limit > $max)?$max:$limit;
    }

    /**
     * states that limit should be applied to the end of the result set
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

        $range = null;
        if ($this->getRequest()->headers) {
            $range = $this->getRequest()->headers->get('Range');
        }

        if (is_null($range)) {
            throw new HttpException(400, 'Bad Request');
        }

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

        if (!is_null($this->limit) && $this->limit <= 0) {
            throw new HttpException(400, 'Bad Request');
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
}
