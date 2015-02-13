<?php
/**
 * Класс для быстрой вставки урла куда угодно
 *
 * @author LiverEnemy
 */

class Url
{
    public static function to($controllerAction, array $params = [], array $data = [])
    {
        return App::serviceRoute()->createUrl($controllerAction, $params, $data);
    }
} 