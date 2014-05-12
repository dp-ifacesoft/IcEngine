<?php

/**
 * Абстрактный парсер логинзы 
 * 
 * @author morph
 */
abstract class Loginza_Parser_Abstract
{
    /**
     * Парсит результат логинзы
     * 
     * @param string $data
     * @return array
     */
    abstract public function parse($data);
}