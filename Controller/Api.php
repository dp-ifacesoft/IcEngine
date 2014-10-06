<?php

/**
 * Контроллер API
 *
 * @author markov
 */
class Controller_Api extends Controller_Abstract
{
    /**
     * Запрос к api
     * 
     * @Route(
     *     "/api/",
     *     "weight"=500,
     *     "params"={
     *         "viewRender"="Json"
     *     }
     * )
     * @Context("helperApi")
     */
    public function index($cmd, $sig, $params, $context)
    {
        $result = $context->helperApi->execute($cmd, $sig, $params);
        $this->output->send($result);
    }
}
