<?php

/**
 * Css парсер
 *
 * @author markov
 * @Static("serviceStaticCssParser")
 */
class Service_Static_Css_Parser extends Service_Abstract
{
    /**
     * Возвращает изображения
     * 
     * @param string $cssText
     * @return array
     */
    public function parseImages($cssText)
    {
        $images = [];
        $matches = [];
        preg_match_all('#sprite:.*?url\(([^)]+)#', $cssText, $matches);
        foreach ($matches[1] as $item) {
            $itemTrimed = trim($item, '\'"');
            if ($this->_isImageUrl($itemTrimed)) {
                $images[] = $itemTrimed;
            }
        }
        return $images;
    }
    
    /**
     * Является ли урлом изображения
     * 
     * @param string $item
     * @return boolean
     */
    protected function _isImageUrl($item) 
    {
        return strpos($item, 'data:') !== 0;
    }
}
