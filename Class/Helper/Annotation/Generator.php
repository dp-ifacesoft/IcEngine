<?php

/**
 * Хелпер для аннотаций типа "Generator"
 * 
 * @author morph
 * @Service("helperAnnotationGenerator")
 */
class Helper_Annotation_Generator
{
    /**
     * Получить тип поля по phpdoc
     * 
     * @param string $doc
     * @return string
     */
    public function getType($doc)
    {
        static $regexp = '#@var ([\w\d_]+)#';
        $matches = array();
        preg_match_all($regexp, $doc, $matches);
        return !empty($matches[1][0]) ? $matches[1][0] : null;
    }
}