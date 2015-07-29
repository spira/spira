<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:38
 */

namespace Spira\Responder\Responder;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Spira\Responder\Contract\ApiResponderInterface;
use Spira\Responder\Contract\TransformerInterface;
use Symfony\Component\HttpFoundation\Response;

class ApiResponder extends BaseResponder implements ApiResponderInterface
{
    /**
     * @var TransformerInterface
     */
    protected $transformer;

    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Respond with a created response and associate a location if provided.
     *
     * @param null|string $location
     *
     * @return Response
     */
    public function created($location = null)
    {
        $response = $this->getResponse();
        $response->setContent(null);
        $response->setStatusCode(201);
        if (! is_null($location)) {
            $response->headers->set('Location', $location);
        }
        return $response;
    }

    /**
     * Respond with a no content response.
     *
     * @param  int  $code
     * @return Response
     */
    public function noContent($code = 204)
    {
        $response = $this->getResponse();
        $response->setContent(null);
        return $response->setStatusCode($code);
    }

    /**
     * Bind a collection to a transformer and start building a response.
     *
     * @param array|Collection $items
     * @param array $parameters
     *
     * @return Response
     */
    public function collection($items, array $parameters = [])
    {
        return $this->collectionWithStatusCode($items);
    }

    /**
     * Bind an item to a transformer and start building a response.
     *
     * @param object $item
     * @param array $parameters
     *
     * @return Response
     */
    public function item($item, array $parameters = [])
    {
        return $this->itemWithStatusCode($item);
    }

    /**
     * Respond with a created response.
     *
     * @param array|Collection $items
     * @param array $parameters
     * @return Response
     */
    public function createdCollection($items, array $parameters = [])
    {
        return $this->collectionWithStatusCode($items, 201);
    }

    /**
     * Respond with a created response.
     *
     * @param object $item
     * @param array $parameters
     *
     * @return Response
     */
    public function createdItem($item, array $parameters = [])
    {
        return $this->itemWithStatusCode($item, 201);
    }

    /**
     * @param $item
     * @param int $code
     * @return Response
     */
    protected function itemWithStatusCode($item, $code = 200)
    {
        $response = $this->getResponse();
        $response->setStatusCode($code);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->encode($this->getTransformer()->transformItem($item)));
        return $response;
    }

    /**
     * @param $items
     * @param int $code
     * @return Response
     */
    protected function collectionWithStatusCode($items, $code = 200)
    {
        $response = $this->getResponse();
        $response->setStatusCode($code);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->encode($this->getTransformer()->transformCollection($items)));
        return $response;
    }

    /**
     * TODO: Implement paginator() method.
     * Bind a paginator to a transformer and start building a response.
     *
     * @param Paginator $paginator
     * @param array $parameters
     *
     * @return Response
     */
    public function paginator(Paginator $paginator, array $parameters = [])
    {
    }


    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param  TransformerInterface  $transformer
     * @return $this
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Json encode
     * @param $data
     * @return string
     */
    protected function encode($data)
    {
        $debug = env('APP_DEBUG', false);
        $prettyPrint = $debug?JSON_PRETTY_PRINT:0;
        return json_encode($data, $prettyPrint);
    }
}
