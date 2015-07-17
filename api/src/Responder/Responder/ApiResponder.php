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
     * @return Response
     */
    public function noContent()
    {
        $response = $this->getResponse();
        $response->setContent(null);
        return $response->setStatusCode(204);
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
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent($this->encode($this->getTransformer()->transformCollection($items)));
        return $response;
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
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent($this->encode($this->getTransformer()->transformItem($item)));
        return $response;
    }

    /**
     * Bind a paginator to a transformer and start building a response.
     *
     * @param Paginator $paginator
     * @param array $parameters
     *
     * @return Response
     */
    public function paginator(Paginator $paginator, array $parameters = [])
    {
        // TODO: Implement paginator() method.
    }


    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param TransformerInterface $transformer
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Json encode
     * @param $data
     * @return string
     */
    protected function encode($data)
    {
        return json_encode($data);
    }
}
