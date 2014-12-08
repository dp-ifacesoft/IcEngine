<?php

/**
 * Хелпер для синхронизации acl модели между конфигом и аннотациями
 * 
 * @author morph
 * @Service("helperModelAclSync")
 */
class Helper_Model_Acl_Sync extends Helper_Abstract
{
    /**
     * Хелпер для работы с acl модели через аннотации
     * 
     * @var Helper_Model_Acl_Annotation
     * @Inject @Generator
     */
    protected $helperModelAclAnnotation;
    
    /**
     * Хелпер для работы с acl модели через конфиг
     * 
     * @var Helper_Model_Acl_Config
     * @Inject @Generator
     */
    protected $helperModelAclConfig;
    
    /**
     * Синхронизировать из аннотаций
     * 
     * @param string $modelName
     */
    public function fromAnnotation($modelName)
    {
        $data = $this->helperModelAclAnnotation->forModel($modelName);
        $this->helperModelAclConfig->rewrite($modelName, $data);
    }
    
    /**
     * Синхронизировать из конфига
     * 
     * @param string $modelName
     */
    public function fromConfig($modelName)
    {
        $data = $this->helperModelAclConfig->forModel($modelName);
        $this->helperModelAclAnnotation->rewrite($modelName, $data);
    }
    
    /**
     * Getter for "helperModelAclAnnotation"
     *
     * @return Helper_Model_Acl_Annotation
     */
    public function getHelperModelAclAnnotation()
    {
        return $this->helperModelAclAnnotation;
    }
        
    /**
     * Setter for "helperModelAclAnnotation"
     *
     * @param Helper_Model_Acl_Annotation helperModelAclAnnotation
     */
    public function setHelperModelAclAnnotation($helperModelAclAnnotation)
    {
        $this->helperModelAclAnnotation = $helperModelAclAnnotation;
    }
    
    
    /**
     * Getter for "helperModelAclConfig"
     *
     * @return Helper_Model_Acl_Config
     */
    public function getHelperModelAclConfig()
    {
        return $this->helperModelAclConfig;
    }
        
    /**
     * Setter for "helperModelAclConfig"
     *
     * @param Helper_Model_Acl_Config helperModelAclConfig
     */
    public function setHelperModelAclConfig($helperModelAclConfig)
    {
        $this->helperModelAclConfig = $helperModelAclConfig;
    }
    
}