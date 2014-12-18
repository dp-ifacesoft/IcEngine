<?php

/**
 * Менеджер фрагментов кэша отображения
 * 
 * @author morph
 * @Service("viewCacheFragmentManager")
 */
class View_Cache_Fragment_Manager extends Manager_Abstract
{
    /**
     * Провайдер для получения кэша
     * 
     * @var Data_Provider_Abstract
     * @Generator
     * @Service(
     *      "dataProviderBlock",
     *      args={"Block"},
     *      source={
     *          name="dataProviderManager",
     *          method="get"
     *      }
     * )
     */
    protected $provider;
    
    /**
     * Получить фрагмент по имени
     * 
     * @param string $name
     * @param array $params
     * @return array
     */
    public function get($name, $params)
    {
        $key = $this->getHashKey($name, $params);
        $params = $this->getParams($params);
        $fragmentConfig = $this->config()[$name];
        $fragment = new View_Cache_Fragment();
        $fragment->setConfig($fragmentConfig);
        $fragment->setContent($this->provider->get($key));
        $fragment->setParams($params);
        return $fragment;
    }
    
    /**
     * Получить ключа для кэша
     * 
     * @param string $name
     * @param array $params
     * @return string
     */
    public function getHashKey($name, $params)
    {
        return $name . md5(json_encode($params));
    }
    
    /**
     * Сформировать аргументы блока
     * 
     * @param array $params
     * @return array
     */
    public function getParams($params)
    {
        $controllerManager = $this->getService('controllerManager');
        $tasks = $controllerManager->getTaskPool();
        $task = end($tasks);
        $params = array_merge($params, $task->getInput()->receiveAll());
        return $tasks;
    }
    
    /**
     * Изменить кэш фрагмента
     * 
     * @param string $name
     * @param string $content
     * @param array $params
     */
    public function set($name, $content, $params)
    {
        $key = $this->getHashKey($name, $params);
        $cache = array(
            'e' => time(),
            'v' => $content
        );
        $this->provider->set($key, $cache);
    }
    
    /**
     * Getter for "provider"
     *
     * @return Data_Provider_Abstract
     */
    public function getProvider()
    {
        return $this->provider;
    }
        
    /**
     * Setter for "provider"
     *
     * @param Data_Provider_Abstract provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }
}