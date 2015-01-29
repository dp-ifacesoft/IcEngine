<?php

/**
 * Помощник для работы с Csv-файлом
 *
 * @author nastya
 * @Service("helperCsv")
 */
class Helper_Csv extends Helper_Abstract
{
    /**
     * Открыть или создать файл
     * @param string $filePath путь к файлу
     * @return указатель на файл
     */
    public function getCsv($filePath)
    {
        $csvFile = fopen($filePath, 'w');
        return $csvFile;
    }
    
    /**
     * Записать данные в файл
     * @param string $filePath путь к файлу
     * @param array $data данные для записи
     * @param string $delimeter разделитель
     * @return bool
     */
    public function writeCsv($filePath, $data, $delimeter)
    {
        $csv = $this->getCsv($filePath);
        foreach ($data as $row) {
            fputcsv($csv, $row, $delimeter);
        }
        fclose($csv);
        return true;
    }
}
