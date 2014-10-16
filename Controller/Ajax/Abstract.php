<?php

/**
 * Базовый класс контроллера аякса
 *
 * @author Apostle
 */
abstract class Controller_Ajax_Abstract extends Controller_Abstract
{
    /**
     * Обновить
     * @Ajax
     * @param mixed $params - параметры падающие из вызывающей функции в js
     */
    abstract public function reload($params);
}
