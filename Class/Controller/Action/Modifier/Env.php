<?php

/**
 * Модификатор состояния действия для изменения окружения
 * 
 * @author morph
 */
class Controller_Action_Modifier_Env extends Controller_Action_Modifier_Abstract
{
    /**
     * Возможные для изменения переменные окружения
     * 
     * @param array
     */
    protected static $data = array(
        'host'  => array('HTTP_HOST', 'SERVER_NAME')
    );
    
    /**
     * @inheritdoc
     */
    public function run($state)
    {
        foreach ($this->args as $arg => $value) {
            if (!isset(self::$data[$arg])) {
                continue;
            }
            foreach ((array) self::$data[$arg] as $argName) {
                $_SERVER[$argName] = $value;
            }
        }
        if (isset($_SERVER['HTTP_HOST'])) {
            $serviceLocator = IcEngine::serviceLocator();
            $dds = $serviceLocator->getService('dds');
            $dataSourceManager = $serviceLocator->getService(
                'dataSourceManager'
            );
            $dataSource = $dataSourceManager->get($_SERVER['HTTP_HOST']);
            $dds->setDataSource($dataSource);
        }
    }
}