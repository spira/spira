<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 30.07.15
 * Time: 14:58
 */

namespace Spira\Responder\Paginator;

use Illuminate\Http\Request;

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
        if ($this->parsed){
            return true;
        }

        $range = $this->getRequest()->headers?$this->getRequest()->headers->get('Range',''):'';
        $ranges = explode('-',$range);
        if (isset($ranges[0]) && $ranges[0] !== ''){
            $this->offset = $ranges[0];
        }

        if (isset($ranges[1]) && $ranges[1] !== ''){
            if (is_null($this->offset)) {
                $this->isGetLast = true;
                $this->limit = $ranges[1];
            }else{
                $this->limit = $ranges[1] - $ranges[0] + 1;
            }
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