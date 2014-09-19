<?php

/**
 * Описание Extended
 *
 * @author Apostle
 */
class DOM_Extended extends DOMDocument
{
    /**
     * Получить элементы по классу
     * @param string $html html
     * @param string $classname class селектор
     * @return DOMNode нода
     */
    public function getElementsByClass($classname)
    {
        $finder = new DomXPath($this);
        return $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
    }
}
