<?php

/**
 * Спецификация для проварки тайтлы
 * 
 * @author morph
 */
abstract class Page_Title_Specification_Abstract 
{
    /**
     * Проверяет удовлетворяет ли спецификация условию
     * 
     * @param array $data
     * @return boolean
     */
    abstract public function isSatisfiedBy($data);
}