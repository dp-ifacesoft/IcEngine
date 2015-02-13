<?php

/**
 * Абстрактный генератор последовательностей
 * 
 * @author morph
 */
abstract class Sequence_Abstract
{
    /**
     * Генерирует следующий элемент последовательности
     * 
     * @param mixed $prev
     * @return mixed
     */
    abstract public function next($prev = null);
}