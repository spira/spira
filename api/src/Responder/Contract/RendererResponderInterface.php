<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:23
 */

namespace Spira\Responder\Contract;

interface RendererResponderInterface extends ResponderInterface
{
    /**
     * @param $template
     * @param array $params
     * @return mixed
     */
    public function render($template, array $params = []);
}
