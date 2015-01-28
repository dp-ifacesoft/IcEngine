<?php

/**
 * Помощник для работы с Csv-файлом
 *
 * @author nastya
 * @Service("helperCsv")
 */
class Helper_Csv extends Helper_Abstract
{
    public function getCsv($filePath)
    {
        if (!file_exists($filePath)) {
            return $this->createCsv($filePath);
        }
        else {
            return $this->loadCsv($filePath);
        }
        return false;
    }
    
    public function createCsv($filePath)
    {
        return $csvFile;
    }
    
    public function loadCsv($filePath)
    {
        return $csvFile;
    }
    
    public function writeCsv($filePath)
    {
        return $result;
    }
}
