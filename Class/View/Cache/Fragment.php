<?php

/**
 * Фрагмент кэша отображения
 * 
 * @author morph
 */
class View_Cache_Fragment
{
    /**
     * Конфигурация фрагмента
     * 
     * @var Config_Array
     * @Generator
     */
    protected $config;
    
    /**
     * Контент
     * 
     * @Generator
     * @var string
     */
    protected $content;
    
    /**
     * Параментры фрагмента
     * 
     * @var array
     * @Generator
     */
    protected $params;
    
    /**
     * Тестовое включение кэширования
     * 
     * @var boolean
     */
    private $testCacheEnabling = true;
    
    /**
     * Получить контент фрагмента
     * 
     * @return string
     */
    public function content()
    {
        if (!$this->content) {
            return null;
        }
        return $this->content['v'];
    }
    
    /**
     * Имеет ли текущий авторизованный пользователь роль "editor"
     * 
     * @return boolean
     */
    protected function isCurrentUserHasRoleEditor()
    {
        $serviceLocator = IcEngine::serviceLocator();
        return $serviceLocator->getService('user')->getCurrent()
            ->hasRole('editor');
    }
    
    /**
     * Просрочен ли кэш фрагмента
     * 
     * @return boolean
     */
    protected function isExpired()
    {
        if (!$this->config->expiration) {
            return false;
        }
        return $this->content['e'] + $this->config->expiration < time();
    }
    
    /**
     * Должен ли кэшировать фрагмент
     * 
     * @return boolean
     */
    protected function isMustCache()
    {
        if (!$this->config->notCache) {
            return true;
        }
        foreach ($this->config->notCache as $param => $value) {
            if (isset($this->params[$param]) && 
                $this->params[$param] == $value) {
                return false;
            }
        }
    }
    
    /**
     * Валиден ли кэш фрагмента
     * 
     * @return boolean
     */
    public function isValid()
    {
        return $this->content &&  
            !$this->isViewFragmentCachingDisabled() &&
            !$this->isCurrentUserHasRoleEditor() &&
            $this->isMustCache() && 
            !$this->isExpired();
    }
    
    /**
     * Отключено ли кэширование фрагментов отображения
     * 
     * @return boolean
     */
    protected function isViewFragmentCachingDisabled()
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperSiteLocation = $serviceLocator->getService('helperSiteLocation');
        return !$helperSiteLocation->get('enabledBlockCache') 
            && $this->testCacheEnabling;
    }
    
    /**
     * Getter for "config"
     *
     * @return Config_Array
     */
    public function getConfig()
    {
        return $this->config;
    }
        
    /**
     * Setter for "config"
     *
     * @param Config_Array config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    
    /**
     * Getter for "content"
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
        
    /**
     * Setter for "content"
     *
     * @param string content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    
    /**
     * Getter for "params"
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
        
    /**
     * Setter for "params"
     *
     * @param array params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
    
}