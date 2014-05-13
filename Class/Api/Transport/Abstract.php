<?php

/**
 * Абстрактный транспорт api
 * 
 * @author morph
 */
abstract class Api_Transport_Abstract
{
    /**
     * Отправить данные через транспорт api
     * 
     * @param mixed $call
     * @param mixed $args
     * @return mixed
     */
    abstract public function send($call, $args);
}