<?php

/**
 * Процесс миграций
 * 
 * @author morph
 * @Service("helperMigrationProcess")
 */
class Helper_Migration_Process extends Helper_Abstract
{
    /**
     * Откат миграции
     * 
     * @param string $to
     * @param string $category
     */
    public function down($to, $category)
    {
        return $this->process($to, $category, 'down');
    }
    
    /**
     * Фоновое выполнение миграции
     *
     * @Todo("Возможно требуется заменить на именно фоновое выполнение") 
     * @param Migration_Abstract $migrationName
     * @param string $action
     */
    protected function exec($migration, $action)
    {
        return call_user_func(array($migration, $action));
    }
    
    /**
     * Процесс поднятия, отката миграции
     * 
     * @param string $to
     * @param string $category
     * @param string $action
     */
    protected function process($to, $category, $action)
    {
        $helperMigrationQueue = $this->getService('helperMigrationQueue');
        $current = $helperMigrationQueue->current($category);
        $queue = $helperMigrationQueue->getQueue($category);
        if ($action == 'down') {
            $queue = array_reverse($queue);
        }
        $isStarted = false;
        foreach (array_keys($queue) as $migrationName) {
            if (!$isStarted && $migrationName != $current['name']) {
                continue;
            } elseif ($migrationName == $current['name']) {
                $isStarted = true;
            } elseif ($migrationName == $to) {
                break;
            } else {
                $result = $this->runMigration(
                    $migrationName, $current['name'], $category, $action
                );
                if (!$result) {
                    break;
                }
            }
        }
    }
    
    /**
     * Откат миграции
     * 
     * @param string $from
     * @param string $to
     * @param string $category
     * @param string $action
     */
    protected function rollback($from, $to, $category, $action)
    {
        if ($action == 'up') {
            $action = 'down';
        } else {
            $action = 'up';
        }
        $helperMigrationQueue = $this->getService('helperMigrationQueue');
        $queue = array_reverse($helperMigrationQueue->getQueue($category));
        if ($action == 'down') {
            $queue = array_reverse($queue);
        }        
        $isStarted = false;
        $migrationManager = $this->getService('migrationManager');
        foreach (array_keys($queue) as $migrationName) {
            if (!$isStarted && $migrationName != $from) {
                continue;
            } elseif ($migrationName == $from) {
                $isStarted = true;
            } elseif ($migrationName == $to) {
                break;
            } else {
                $migration = $migrationManager->get($migrationName);
                $this->exec($migration, $action);
            }
        }
    }
    
    /**
     * Выполнить миграцию
     * 
     * @param string $migrationName
     * @param string $currentName
     * @param string $category
     * @param string $action
     */
    protected function runMigration($migrationName, $currentName, 
        $category, $action)
    {
        $migrationManager = $this->getService('migrationManager');
        $migration = $migrationManager->get($migrationName);
        $result = $this->exec($migration, $action);
        if (!$result) {
            $this->rollback($migrationName, $currentName, $category, $action);
            return false;
        } else {
            $migration->log($action);
        }
        return true;
    }
    
    /**
     * Поднятие миграции
     * 
     * @param string $to
     * @param string $category
     */
    public function up($to, $category)
    {
        return $this->process($to, $category, 'up');
    }
    
    /**
     * Проверка валидности поднятия/отката миграций
     * 
     * @param string $to
     * @param string $category
     * @param string $action
     * @return boolean
     */
    protected function valid($to, $category, $action = 'up')
    {
        $helperMigrationQueue = $this->getService('helperMigrationQueue');
        $queue = $helperMigrationQueue->getQueue($category);
        if (!$queue) {
            return false;
        }
        $current = $helperMigrationQueue->current($category);
        if ($action == 'down' && !$current) {
            return false;
        }
        if (!isset($queue[$to])) {
            return false;
        }
        $currentPos = 0;
        $toPos = 0;
        foreach (array_keys($queue) as $i => $migrationName) {
            if ($migrationName == $to) {
                $toPos = $i;
            } elseif ($migrationName == $category['name']) {
                $currentPos = $i;
            }
        }
        if ($action ==  'up') {
            $tmp = $toPos;
            $toPos = $currentPos;
            $currentPos = $tmp;
        }
        if ($toPos < $currentPos) {
            return false;
        }
        return true;
    }
    
    /**
     * Проверить валидность отката миграции
     * 
     * @param string $to
     * @param string $category
     * @return boolean
     */
    public function validDown($to, $category)
    {
        return $this->valid($to, $category, 'down');
    }
    
    /**
     * Проверить валидность поднятия миграции
     * 
     * @param string $to
     * @param string $category
     * @return boolean
     */
    public function validUp($to, $category)
    {
        return $this->valid($to, $category, 'up');
    }
}