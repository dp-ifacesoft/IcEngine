<?php

/**
 * Помощник для работы с классами
 *
 * @author Apostle
 * @Service("helperClass")
 */
class Helper_Class extends Helper_Abstract 
{
    /**
     * получить имя класса без префикса пути
     * @param Class $class экземпляр некого класса
     */
    public function getClassTail($class, $lower = TRUE)
    {
        $pos = strripos($class, '_');
            if ($pos !== false) {
                if ($lower) {
                    return strtolower(substr($class, (int)($pos+1)));
                }
                return substr($class, (int)($pos+1));
            }
        return $class;
    }
}
