<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 02.08.15
 * Time: 23:26
 */

namespace Spira\Responder\Response;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spira\Responder\Contract\TransformerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiResponse extends Response
{
    /**
     * Respond with a created response and associate a location if provided.
     *
     * @param null|string $location
     *
     * @return Response
     */
    public function created($location = null)
    {
        $this->setContent(null);
        $this->setStatusCode(201);
        if (! is_null($location)) {
            $this->headers->set('Location', $location);
        }
        return $this;
    }


    /**
     * Respond with a no content response.
     *
     * @param  int  $code
     * @return Response
     */
    public function noContent($code = 204)
    {
        $this->setContent(null);
        return $this->setStatusCode($code);
    }


    /**
     * Bind a collection to a transformer and start building a response.
     *
     * @param array|Collection $items
     * @param TransformerInterface $transformer
     * @param array $parameters
     * @return Response
     */
    public function collection($items, TransformerInterface $transformer, array $parameters = [])
    {
        return $this->collectionWithStatusCode($items, $transformer);
    }


    /**
     * Respond with a created response.
     *
     * @param array|Collection $items
     * @param TransformerInterface $transformer
     * @param array $parameters
     * @return Response
     */
    public function createdCollection($items, TransformerInterface $transformer, array $parameters = [])
    {
        foreach ($items as $item) {
            $item->setVisible(['']);
        }
        return $this->collectionWithStatusCode($items, $transformer, 201);
    }

    /**
     * Bind an item to a transformer and start building a response.
     *
     * @param object|string $item
     * @param TransformerInterface $transformer
     * @param array $parameters
     * @return Response
     */
    public function item($item, TransformerInterface $transformer, array $parameters = [])
    {
        return $this->itemWithStatusCode($item, $transformer);
    }


    /**
     * Respond with a created response.
     *
     * @param object $item
     * @param TransformerInterface $transformer
     * @param array $parameters
     * @return Response
     */
    public function createdItem($item, TransformerInterface $transformer, array $parameters = [])
    {
        $item->setVisible(['']);
        return $this->itemWithStatusCode($item, $transformer, 201);
    }

    /**
     * @param $item
     * @param TransformerInterface $transformer
     * @param int $code
     * @return Response
     */
    protected function itemWithStatusCode($item, TransformerInterface $transformer, $code = 200)
    {
        $this->setStatusCode($code);
        $this->headers->set('Content-Type', 'application/json');
        $this->setContent($this->encode($transformer->transformItem($item)));
        return $this;
    }

    /**
     * @param $items
     * @param TransformerInterface $transformer
     * @param int $code
     * @return Response
     */
    protected function collectionWithStatusCode($items, TransformerInterface $transformer, $code = 200)
    {
        $this->setStatusCode($code);
        $this->headers->set('Content-Type', 'application/json');
        $this->setContent($this->encode($transformer->transformCollection($items)));

        return $this;
    }


    /**
     * Build paginated response.
     *
     * @param Collection|array $items
     * @param null|int $offset
     * @param null|int $totalCount
     * @param TransformerInterface $transformer
     * @param array $parameters
     * @return Response
     * @throws HttpException
     */
    public function paginatedCollection($items, TransformerInterface $transformer, $offset = null, $totalCount = null, array $parameters = [])
    {
        $itemCount = count($items);
        $this->validateRange($itemCount);

        $this->headers->set('Accept-Ranges', 'entities');
        $this->headers->set('Content-Type', 'application/json');
        $this->setStatusCode(206);

        $rangeHeader = $this->prepareRangeHeader($itemCount, $offset, $totalCount);
        $this->headers->set('Content-Range', $rangeHeader);

        $this->setContent($this->encode($transformer->transformCollection($items)));


        return $this;
    }

    /**
     * @param $itemCount
     * @return bool
     */
    protected function validateRange($itemCount)
    {
        if ($itemCount <= 0) {
            throw new HttpException(416, 'Requested Range Not Satisfiable');
        }

        return true;
    }

    /**
     * @param $itemCount
     * @param $offset
     * @param $totalCount
     * @return array
     */
    protected function prepareRangeHeader($itemCount, $offset, $totalCount)
    {
        $offset = is_null($offset) ? 0 : $offset;
        $totalCount = is_null($totalCount) ? '*' : $totalCount;
        $rangeHeader = 'entities '.$offset . '-' . ($itemCount + $offset - 1) . '/' . $totalCount;

        return $rangeHeader;
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