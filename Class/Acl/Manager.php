<?php

/**
 * Менеджер для работы с acl
 * 
 * @author morph
 * @Service("aclManager")
 */
class Acl_Manager extends Manager_Abstract
{
    /**
     * Провайдер acl данных
     * 
     * @Generator
     * @Service(
     *      "aclProviderTable",
     *      args={"Table"},
     *      source={
     *          name="aclProviderManager",
     *          method="get"
     *      }
     * )
     * @var Acl_Provider_Abstract
     */
    protected $aclProvider;
    
    /**
     * Getter for "aclProvider"
     *
     * @return Acl_Provider_Abstract
     */
    public function getAclProvider()
    {
        return $this->aclProvider;
    }
        
    /**
     * Setter for "aclProvider"
     *
     * @param Acl_Provider_Abstract aclProvider
     */
    public function setAclProvider($aclProvider)
    {
        $this->aclProvider = $aclProvider;
    }
    
}