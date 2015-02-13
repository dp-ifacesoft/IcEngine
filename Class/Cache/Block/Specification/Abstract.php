<?php

/**
 * Абстрактная спецификация для блоков кэширования
 * 
 * @author morph
 */
abstract class Cache_Block_Specification_Abstract
{
    /**
     * Удовлетворяет ли условие
     * 
     * @return boolean
     */
    abstract public function isSatisfiedBy();
}