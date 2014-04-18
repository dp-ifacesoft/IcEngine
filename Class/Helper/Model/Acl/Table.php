<?php

/**
 * Хелпер для работы с данными acl через таблицу базы данных
 * 
 * @author morph
 * @Service("helperModelAclTable")
 */
class Helper_Model_Acl_Table extends Helper_Abstract
{
    /**
     * Получить acl для модели
     * 
     * @param mixed $model
     * @return array
     */
    public function forModel($model)
    {
        $modelName = is_string($model) ? $model : $model->modelName();
        $tableName = $this->getService('modelScheme')->table($modelName);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $roles = $this->getService('collectionManager')->create('Acl_Role')
            ->raw(array('id', 'name')); 
        $indexedRoles = $this->getService('helperArray')->reindex($roles, 'id');
        $preResourceQuery = $queryBuilder
            ->select('Acl_Resource.id')
            ->from('Acl_Resource')
            ->where('name LIKE ?', 'Table/' . $tableName . '/%');
        $preResourceIds = $dds->execute($preResourceQuery)->getResult()
            ->asColumn();
        $resourceIdQuery = $queryBuilder
            ->select('fromRowId', 'toRowId')
            ->from('Link')
            ->where('fromTable', 'Acl_Resource')
            ->where('toTable', 'Acl_Role')
            ->where('fromRowId', $preResourceIds)
            ->where('toRowId', array_keys($indexedRoles));
        $resourceIds = $dds->execute($resourceIdQuery)->getResult()
            ->asTable('fromRowId');
        $resourceQuery = $queryBuilder
            ->select('id', 'name')
            ->from('Acl_Resource')
            ->where('id', array_keys($resourceIds))
            ->where('name LIKE ?', 'Table/' . $tableName . '/%');
        $resources = $dds->execute($resourceQuery)->getResult()->asTable('id');
        $roleResourceIds = array();
        foreach ($resourceIds as $resourceId => $row) {
            $roleId = $row['toRowId'];
            if (!isset($roleResourceIds[$roleId])) {
                $roleResourceIds[$roleId] = array();
            }
            $roleResourceIds[$roleId][] = $resourceId;
        }
        $result = array();
        foreach ($resources as $resourceId => $row) {
            $name = $row['name'];
            list(,, $fieldName, $accessType) = explode('/', $name);
            $exists = false;
            foreach ($roleResourceIds as $roleId => $resourceIds) {
                if (in_array($resourceId, $resourceIds)) {
                    $exists = true;
                    break;
                }
            } 
            if (!$exists) {
                continue;
            }
            $roleName = $indexedRoles[$roleId]['name'];
            if (!isset($result[$fieldName])) {
                $result[$fieldName] = array();
            }
            if (!isset($result[$fieldName][$roleName])) {
                $result[$fieldName][$roleName] = array();
            }
            $result[$fieldName][$roleName][] = $accessType;
        }
        return $result;
    }
    
    /**
     * Переписать acl для модели
     * 
     * @param mixed $model
     * @param array $data
     */
    public function rewrite($model, $data)
    {
        $modelName = is_string($model) ? $model : $model->modelName();
        $tableName = $this->getService('modelScheme')->table($modelName);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $resourceQuery = $queryBuilder
            ->select('Acl_Resource.id')
            ->from('Acl_Resource')
            ->where('name LIKE ?', 'Table/' . $tableName . '/%');
        $resourceIds = $dds->execute($resourceQuery)->getResult()->asColumn();
        if ($resourceIds) {
            $resouceDeleteQuery = $queryBuilder
                ->delete()
                ->from('Acl_Resource')
                ->where('id', $resourceIds);
            $dds->execute($resouceDeleteQuery);
            $linkDeleteQuery = $queryBuilder
                ->delete()
                ->from('Link')
                ->where('fromTable', 'Acl_Resource')
                ->where('fromRowId', $resourceIds)
                ->where('toTable', 'Acl_Role');
            $dds->execute($linkDeleteQuery);
        }
        $unitOfWork = $this->getService('unitOfWork');
        $unitOfWork->setAutoflush(500);
        $maxResourceQuery = $queryBuilder
            ->select('Acl_Resource.id')
            ->from('Acl_Resource')
            ->order('id DESC')
            ->limit(1);
        $maxResourceId = $dds->execute($maxResourceQuery)->getResult()
            ->asValue();
        $rows = array();
        foreach ($data as $fieldName => $roles) {
            foreach ($roles as $roleName => $accessTypes) {
                $resourceName = 'Table/' . $tableName . '/' . $fieldName . '/' .
                    $accessTypes;
                if (isset($rows[$resourceName])) {
                    $rows[$resourceName] = array();
                }
                $rows[$resourceName][] = $roleName;
            }
        }
        $existsRoles = $this->getService('collectionManager')
            ->create('Acl_Role')->raw();
        $indexedRoles = $this->getService('helperArray')->reindex(
            $existsRoles, 'name'
        );
        foreach ($rows as $resourceName => $roles) {
            $maxResourceId++;
            $resourceQuery = $queryBuilder
                ->insert('Acl_Resource')
                ->values(array(
                    'id'    => $maxResourceId,
                    'name'  => $resourceName
                ));
            $unitOfWork->push($resourceQuery);
            foreach ($roles as $roleName) {
                if (!isset($indexedRoles[$roleName])) {
                    continue;
                }
                $roleId = $indexedRoles[$roleName];
                $linkQuery = $queryBuilder
                    ->insert('Link')
                    ->values(array(
                        'fromTable' => 'Acl_Resource',
                        'fromRowId' => $maxResourceId,
                        'toTable'   => 'Acl_Role',
                        'toRowId'   => $roleId
                    ));
                $unitOfWork->push($linkQuery);
            }
        }
        $unitOfWork->flush();
    }
}