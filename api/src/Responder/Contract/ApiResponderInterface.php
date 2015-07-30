<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:24
 * inspired by dingo api
 */

namespace Spira\Responder\Contract;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

interface ApiResponderInterface extends ResponderInterface
{
    /**
     * Respond with a created response and associate a location if provided.
     *
     * @param null|string $location
     *
     * @return Response
     */
    public function created($location = null);


    /**
     * Respond with a no content response.
     *
     * @return Response
     */
    public function noContent();

    /**
     * @param TransformerInterface $transformer
     */
    public function setTransformer($transformer);

    /**
     * @return TransformerInterface
     */
    public function getTransformer();

    /**
     * Bind a collection to a transformer and start building a response.
     *
     * @param array|Collection $items
     * @param array $parameters
     * @return Response
     */
    public function collection($items, array $parameters = []);

    /**
     * Respond with a created response.
     *
     * @param array|Collection $items
     * @param array $parameters
     * @return Response
     */
    public function createdCollection($items, array $parameters = []);

    /**
     * Bind an item to a transformer and start building a response.
     *
     * @param object   $item
     * @param array    $parameters
     *
     * @return Response
     */
    public function item($item, array $parameters = []);

    /**
     * Respond with a created response.
     *
     * @param object   $item
     * @param array    $parameters
     *
     * @return Response
     */
    public function createdItem($item, array $parameters = []);


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
    public function paginatedCollection($items, $offset = null, $totalCount = null, array $parameters = []);
}
