<?php

/**
 * Хелпер для правил доступа к моделям
 * 
 * @author morph
 * @Service("helperModelAcl")
 */
class Helper_Model_Acl extends Helper_Abstract
{
    /**
     * Типы доступа
     * 
     * @var array
     */
    protected static $accessTypes = array(
        'show', 'create', 'edit'
    );
    
    /**
     * Для модели сравнить acl из конфига и acl из аннотаций
     * 
     * @param string $modelName
     * @return boolean
     */
    public function compare($modelName)
    {
        $aclConfig = $this->getService('helperModelAclConfig')
            ->forModel($modelName);
        $aclAnnotations = $this->getService('helperModelAclAnnotation')
            ->forModel($modelName);
        if (count($aclAnnotations) != count($aclConfig)) {
            return false;
        }
        foreach ($aclAnnotations as $fieldName => $aclAnnotationRoles) {
            if (!isset($aclConfig[$fieldName])) {
                return false;
            }
            $aclConfigRoles = $aclConfig[$fieldName];
            ksort($aclAnnotationRoles);
            ksort($aclConfigRoles);
            if (array_diff_key($aclAnnotationRoles, $aclConfigRoles) ||
                count($aclConfigRoles) != count($aclAnnotationRoles)) {
                return false;
            }
            foreach ($aclAnnotationRoles as $roleName => $annotationAccessTypes) {
                if (!isset($aclConfigRoles[$roleName])) {
                    return false;
                }
                $configAccessTypes = $aclConfigRoles[$roleName];
                if (array_diff($configAccessTypes, $annotationAccessTypes) ||
                    count($configAccessTypes) != count($annotationAccessTypes)) {
                    return false;
                }
            }
        } 
        return true;
    }
    
    /**
     * Получить права доступа для поля модели
     * 
     * @param mixed $model
     * @param string $field
     * @return array
     */
    public function forField($model, $field)
    {
        $modelName = is_string($model) ? $model : $model->modelName();
        $config = $this->getService('helperModelAclConfig')
            ->getConfig($modelName);
        return isset($config[$field]) ? $config[$field] : array();
    }
    
    /**
     * Получить виды прав доступа
     * 
     * @return array
     */
    public function getAccessTypes()
    {
        return self::$accessTypes;
    }
}