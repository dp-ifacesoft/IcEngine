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
    
    /**
     * Преобразует дату к нужному формату
     * @param date $date дата
     */
    public function formatDate($date)
    {
        return date("D, j M Y G:i:s", strtotime($date)). " GMT";
    }
}
