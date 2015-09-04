<?php

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 02.08.15
 * Time: 23:26.
 */

namespace Spira\Responder\Response;

use InvalidArgumentException;
use Illuminate\Http\Response;
use Spira\Responder\Contract\TransformerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiResponse extends Response
{
    /** @var TransformerInterface */
    protected $transformer = null;

    /**
     * Set the transformer to use for building entities.
     * @param TransformerInterface $transformer
     * @return ApiResponse
     */
    public function transformer(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Respond with a created response and associate a location if provided.
     * @param null $location
     * @return ApiResponse
     */
    public function created($location = null)
    {
        if (! is_null($location)) {
            $this->header('Location', $location);
        }

        return $this
            ->setContent(null)
            ->setStatusCode(self::HTTP_CREATED);
    }

    /**
     * Respond with a no content response.
     *
     * @param  int  $code
     * @return ApiResponse
     */
    public function noContent($code = self::HTTP_NO_CONTENT)
    {
        return $this
            ->setStatusCode($code)
            ->setContent(null);
    }

    /**
     * Bind an item to a transformer and start building a response.
     * @param $item
     * @param int $statusCode
     * @return ApiResponse
     */
    public function item($item, $statusCode = self::HTTP_OK)
    {
        if ($this->transformer) {
            $item = $this->transformer->transformItem($item);
        }

        return $this
            ->header('Content-Type', 'application/json')
            ->setContent($this->encode($item))
            ->setStatusCode($statusCode);
    }

    /**
     * Respond with a created response.
     * @param $item
     * @return ApiResponse
     */
    public function createdItem($item)
    {
        $item->setVisible(['']);

        return $this->item($item, self::HTTP_CREATED);
    }

    /**
     * @param $items
     * @param int $statusCode
     * @return ApiResponse
     */
    public function collection($items, $statusCode = Response::HTTP_OK)
    {
        if ($this->transformer) {
            $items = $this->transformer->transformCollection($items);
        }

        return $this
            ->header('Content-Type', 'application/json')
            ->setContent($this->encode($items))
            ->setStatusCode($statusCode);
    }

    /**
     * Respond with a created response and hide all the items (except self).
     * @param $items
     * @return ApiResponse
     */
    public function createdCollection($items)
    {
        foreach ($items as $item) {
            $item->setVisible(['']);
        }

        return $this->collection($items, self::HTTP_CREATED);
    }

    /**
     * Build paginated response.
     * @param $items
     * @param null $offset
     * @param null $totalCount
     * @return ApiResponse
     */
    public function paginatedCollection($items, $offset = null, $totalCount = null)
    {
        $itemCount = count($items);
        $this->validateRange($itemCount);

        $rangeHeader = $this->prepareRangeHeader($itemCount, $offset, $totalCount);

        return $this
            ->header('Accept-Ranges', 'entities')
            ->header('Content-Type', 'application/json')
            ->header('Content-Range', $rangeHeader)
            ->collection($items, self::HTTP_PARTIAL_CONTENT);
    }

    /**
     * @param $itemCount
     * @return bool
     */
    protected function validateRange($itemCount)
    {
        if ($itemCount <= 0) {
            throw new HttpException(self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, 'Requested Range Not Satisfiable');
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
        $rangeHeader = 'entities '.$offset.'-'.($itemCount + $offset - 1).'/'.$totalCount;

        return $rangeHeader;
    }

    /**
     * Json encode.
     * @param $data
     * @return string
     */
    protected function encode($data)
    {
        $debug = env('APP_DEBUG', false);
        $prettyPrint = $debug ? JSON_PRETTY_PRINT : 0;

        return json_encode($data, $prettyPrint);
    }

    /**
     * Creates a redirect response.
     *
     * @param  string  $url
     * @param  int     $status
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function redirect($url, $status = 302)
    {
        if (empty($url)) {
            throw new InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->setStatusCode($status);
        $this->header('Location', $url);

        if (! $this->isRedirect()) {
            throw new InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }
    }
}
