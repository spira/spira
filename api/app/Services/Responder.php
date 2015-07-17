<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 17:36
 */

namespace App\Services;

use App\Http\Transformers\TransformerInterface;
use Illuminate\Http\Request;

class Responder
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var TransformerInterface
     */
    protected $transformer;

    public function __construct(Request $request, TransformerInterface $transformer)
    {
        $this->request = $request;
        $this->transformer = $transformer;
    }
}
