<?php

/**
 * Менеджер кэшей
 * 
 * @author goorus
 * @Service("cacheManager")
 */
class Cache_Manager
{
    /**
     * Кэшеры
     * 
     * @var array <Data_Provider_Abstract>
     */
    protected $cachers = array();
    
    /**
     * Получить кэшеров класса
     * 
     * @param string $class
     * @return Data_Provider_Abstract
     */
    public function cacherFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (!isset($this->cachers[$class])) {
            $this->cachers[$class] = new Data_Provider_Abstract();
        }
        return $this->cachers[$class]; 
    }
    
    /**
     * Загрузить конфиг кэшеров
     * 
     * @param string $file
     * @return Cache_Options
     */
    public function loadConfig($file)
    {
        if (!file_exists($file)) {
            return null;
        }
        $options = new Cache_Options();
        $config = new Config_Php($file);
        $options->applyConfig($config);
        return $options;
    }
}