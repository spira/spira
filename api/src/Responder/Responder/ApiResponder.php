<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:38
 */

namespace Spira\Responder\Responder;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spira\Responder\Contract\ApiResponderInterface;
use Spira\Responder\Contract\TransformerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiResponder extends BaseResponder implements ApiResponderInterface
{
    /**
     * @var TransformerInterface
     */
    protected $transformer;
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request, TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
        $this->request = $request;
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
     * Build paginated response.
     *
     * @param Collection|array $items
     * @param null|int $offset
     * @param null|int $totalCount
     * @param array $parameters
     *
     * @return Response
     */
    public function paginatedCollection($items, $offset = null, $totalCount = null, array $parameters = [])
    {
        $itemCount = count($items);
        $this->validateRange($itemCount);

        $response = $this->getResponse();
        $response->headers->set('Accept-Ranges', 'entities');
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(206);

        $rangeHeader = $this->prepareRangeHeader($itemCount, $offset, $totalCount);
        $response->headers->set('Content-Range', $rangeHeader);

        $response->setContent($this->encode($this->getTransformer()->transformCollection($items)));



        return $response;
    }

    /**
     * @param $itemCount
     * @return bool
     */
    protected function validateRange($itemCount)
    {
        if ($this->request->headers && $this->request->headers->has('Range')) {
            if ($itemCount <= 0) {
                throw new HttpException(416, 'Requested Range Not Satisfiable');
            }
        } else {
            throw new HttpException(400, 'Bad Request');
        }
        return true;
    }


    /**
     * @param $itemCount
     * @param $offset
     * @param $totalCount
     * @return array
     */
    public function prepareRangeHeader($itemCount, $offset, $totalCount)
    {
        $offset = is_null($offset) ? 0 : $offset;
        $totalCount = is_null($totalCount) ? '*' : $totalCount;
        $rangeHeader = $offset . '-' . ($itemCount + $offset - 1) . '/' . $totalCount;

        return $rangeHeader;
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
