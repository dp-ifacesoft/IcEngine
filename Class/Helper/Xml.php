<?php

/**
 * Помощник для экранирования данных для xml файла
 *
 * @author nastya
 * @Service("helperXml")
 */
class Helper_Xml extends Helper_Abstract
{
    /**
     * Текстовые поля
     */
    public function escapeText($text)
    {
        return htmlspecialchars($text);
    }
}
