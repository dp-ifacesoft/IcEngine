<?php

/**
 * Хелпер для работы с конфигурацией acl модели
 * 
 * @author morph
 * @Service("helperModelAclConfig")
 */
class Helper_Model_Acl_Config extends Helper_Abstract
{
    /**
     * Путь до конфигов acl модели
     * 
     * @var string
     */
    protected static $path = 'Ice/Config/Acl/Model/';
    
    /**
     * Получить аннотации acl для модели
     * 
     * @param mixed $model
     * @return array
     */
    public function forModel($model)
    {
        $modelName = is_string($model) ? $model : $model->modelName();
        $config = $this->getService('configManager')->get(
            'Acl_Model_' . $modelName
        );
        return $config ? $config->__toArray() : array();
    }
    
    /**
     * Пересобрать конфиг acl для модели
     * 
     * @param string $modelName
     * @param array $data
     */
    public function rewrite($modelName, $data)
    {
        $helperCodeGenerator = $this->getService('helperCodeGenerator');
        $output = $helperCodeGenerator->fromTemplate('modelAcl', array_merge(
            array('modelName' => $modelName), array('data' => $data)
        ));
        $filename = self::$path . $this->getService('helperModel')
            ->makePath($modelName);
        $this->output($filename, $output);
    }
    
    /**
     * Переписать конфиг
     * 
     * @param string $filename
     * @param string $output
     */
    protected function output($filename, $output)
    {
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 077, true);
        }
        file_put_contents($filename, $output);
    }
}