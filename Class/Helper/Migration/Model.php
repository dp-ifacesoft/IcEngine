<?php

/**
 * Хелпер для миграции моделей
 * 
 * @author morph
 * @Service("helperMigrationModel")
 * @Injectable
 */
class Helper_Migration_Model extends Helper_Abstract
{
    /**
     * Получить метри миграции
     * 
     * @param string $migrationName
     * @Inject("helperMigrationQueue")
     * @return array
     */
    public function getMarks($migrationName, $helperMigrationQueue)
    {
        $filename = $helperMigrationQueue->getFileName($migrationName);
        $marks = $helperMigrationQueue->getMatch($filename, 'marks');
        if (!$marks) {
            return array();
        }
        $data = explode(',', $marks);
        array_map('trim', $data);
        return $data;
    }
    
    /**
     * Поменить миграцию
     * 
     * @param string $migrationName
     * @param string $markName
     * @Inject("helperMigrationQueue")
     * @return boolean
     */
    public function mark($migrationName, $markName, $helperMigrationQueue)
    {
        $marks = $this->getMarks($migrationName, $helperMigrationQueue);
        if (in_array($markName, $marks)) {
            return true;
        }
        $marks[] = $markName;
        $marksContent = implode(',', $marks);
        $filename = $helperMigrationQueue->getFileName($migrationName);
        $content = file_get_contents($filename);
        $regexp = '#@marks(.*?)#';
        $output = preg_replace($regexp, '@marks ' . $marksContent, $content);
        file_put_contents($filename, $output);
    } 
}