<?php

/**
 * Хелпер для работы с очередями миграций
 * 
 * @author morph
 * @Service("helperMigrationQueue")
 */
class Helper_Migration_Queue extends Helper_Abstract
{
    /**
     * Получить текущую миграцию в категории
     * 
     * @param string $category
     * @return array
     */
    public function current($category)
    {
        $queue = $this->getQueue($category);
        $current = null;
        reset($queue);
        while (current($queue)['isFinished']) {
            $current = next($queue);
        }
        return $current;
    }
    
    /**
     * Получить категорию миграции
     * 
     * @param string $file
     * @return string
     */
    protected function getCategory($file)
    {
        return $this->getMatch($file, 'category');
    }
    
    /**
     * Получить путь до миграции по ее имени
     * 
     * @param string $migrationName
     * @return string 
     */
    public function getFileName($migrationName)
    {
        return IcEngine::root() . 'Ice/Model/Migration/' . 
            $migrationName . '.php';
    }
    
    /**
     * Получить список возможных файлов миграции
     * 
     * @return array
     */
    protected function getFiles()
    {
        $path = IcEngine::root() . 'Ice/Model/Migration/';
        $files = scandir($path);
        $resultFiles = array_values(array_slice($files, 2));
        foreach ($resultFiles as $i => $filename) {
            $resultFiles[$i] = $path . $filename;
        }
        return $resultFiles;
    }
    
    /**
     * Получить аннотацию из файла
     * 
     * @param string $file
     * @param string $annotation
     * @return string
     */
    public function getMatch($file, $annotation)
    {
        $regexp = '#@' . $annotation . ' ([\d\w]+)#';
        $matches = array();
        $content = file_get_contents($file);
        preg_match_all($regexp, $content, $matches);
        return !empty($matches[1][0]) ? $matches[1][0] : null;
    }
    
    /**
     * Получить название миграции
     * 
     * @param string $file
     * @return string 
     */
    protected function getName($file)
    {
        $regexp = '#class Migration_([^ ]+)#';
        $matches = array();
        $content = file_get_contents($file);
        preg_match_all($regexp, $content, $matches);
        return !empty($matches[1][0]) ? $matches[1][0] : null;
    }
    
    /**
     * Получить очередь миграций по категории
     * 
     * @param string $category
     */
    public function getQueue($category)
    {
        $files = $this->getFiles();
        $queue = array();
        $migrationManager = $this->getService('migrationManager');
        foreach ($files as $file) {
            $migrationCategory = $this->getCategory($file);
            if (!$migrationCategory || $migrationCategory != $category) {
                continue;
            }
            $migrationName = $this->getName($file);
            if (!$migrationName) {
                continue;
            }
            $migration = $migrationManager->get($migrationName);
            $queue[$migrationName] = $this->lastFor($migration);
        }
        return $this->sortQueue($queue);
    }
    
    /**
     * Получить последние данные для миграции
     * 
     * @param Migration_Abstract $migration
     */
    public function lastFor($migration)
    {
        $migrationName = $migration->getName();
        $filename = $this->getFileName($migrationName);
        $sequence = $this->getMatch($filename, 'seq');
        $status = $this->getService('helperMigrationLog')->status(
            $migrationName, 'up'
        );
        $isMarked = $this->getService('helperMigrationMark')->isMarked(
            $migrationName
        );
        return array(
            'name'          => $migrationName,
            'modelName'     => $migration->model,
            'isFinished'    => $status,
            'isMarked'      => $isMarked,
            'sequence'      => $sequence
        );
    }
    
    /**
     * Отсортировать очередь по последовательности создания
     * 
     * @param array $queue
     * @return array
     */
    protected function sortQueue($queue)
    {
        $helperArray = $this->getService('helperArray');
        return $helperArray->masort($queue, 'sequence');
    }
}