<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\Api\Vanilla;

use Guzzle\Common\Event;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Error
{
    /**
     * Handle request errors.
     *
     * @param  Event $event
     *
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     * @throws MethodNotAllowedHttpException
     * @throws HttpException
     *
     * @return void
     */
    public function onRequestError(Event $event)
    {
        $request = $event['request'];
        $response = $request->getResponse();

        if ($response->isError()) {
            switch ($response->getStatusCode()) {
                case 400:
                    throw new BadRequestHttpException($response->getReasonPhrase());
                    break;
                case 401:
                    throw new UnauthorizedHttpException(null, $response->getReasonPhrase());
                    break;
                case 404:
                    throw new NotFoundHttpException($response->getReasonPhrase());
                    break;
                case 405:
                    throw new MethodNotAllowedHttpException([], $response->getReasonPhrase());
                    break;
                default:
                    throw new HttpException($response->getStatusCode(), $response->getReasonPhrase());
            }
        }
    }
}
