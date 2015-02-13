<?php

/**
 * Хелпер для логирования миграций
 * 
 * @author morph
 * @Service("helperMigrationLog")
 */
class Helper_Migration_Log extends Helper_Abstract
{
    /**
     * Логировать действие миграции
     * 
     * @param string $migrationName
     * @param string $action
     */
    public function log($migrationName, $action)
    {
        $state = array();
        $filename = IcEngine::root() . 'Ice/Var/Migration/state.json';
        if (is_file($filename)) {
            $state = json_decode(file_get_contents($filename), true);
        }
        $date = $this->getService('helperDate')->toUnix();
        $state[$migrationName] = array(
            'name'      => $migrationName,
            'action'    => $action,
            'date'      => $date
        );
        file_put_contents($filename, json_encode($state));
    }
    
    /**
     * Узнать выполнялась ли миграция
     * 
     * @param string $migrationName
     * @param string $action
     * @boolean
     */
    public function status($migrationName, $action = null)
    {
        $filename = IcEngine::root() . 'Ice/Var/Migration/state.json';
        if (!is_file($filename)) {
            return false;
        }
        $state = json_decode(file_get_contents($filename), true);
        $status = isset($state[$migrationName]);
        if (!$status) {
            return false;
        }
        if ($action) {
            $status &= $state[$migrationName]['action'] == $action;
        }
        return $status;
    }
}