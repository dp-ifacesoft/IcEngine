<?php

/**
 * Хелпер по созданию таблиц Mysql
 *
 * @author morph
 * @Service("helperModelTable")
 */
class Helper_Model_Table extends Helper_Abstract
{
    /**
     * Существующие таблицы
     * 
     * @var array
     */
    protected static $tables = array();
    
    /**
     * Создать таблицу для подели
     *
     * @param string $modelName
     */
    public function create($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $scheme = $this->getService('configManager')->get(
            'Model_Mapper_' . $modelName
        );
        if ($scheme->fields) {
            $modelMapperSchemeRenderView = $this->getService(
                'modelMapperSchemeRender'
            );
            $dataSource = $modelScheme->dataSource($modelName);
            $this->getService('dataSourceManager')->initDataDriver($dataSource);
            $driver = $dataSource->getDataDriver();
            if (strpos(get_class($driver), 'Mysql') === false) {
                return;
            }
            $view = $modelMapperSchemeRenderView->byName('Mysql');
            $query = $view->render($modelName);
            $dataSource->execute($query);
        }
    }
    
    /**
     * Проверить таблицу на существование
     * 
     * @param string $className
     * @return boolean
     */
    public function exists($className)
    {
        if (!self::$tables) {
            self::$tables = $this->getTables();
        }
        return isset(self::$tables[$className]);
    }
    
    /**
     * Получить таблицы
     * 
     * @return array
     */
    public function getTables()
    {
        $dds = $this->getService('dds');
        $queryBuilder = $this->getService('queryBuilder');
        $modelScheme = $this->getService('modelScheme');
        $query = $queryBuilder
            ->show('TABLES');
        $tables = $dds->execute($query)->getResult()->asColumn();
        $result = array();
        foreach ($tables as $table) {
            $result[$modelScheme->tableToModel($table)] = true;
        }
        return $result;
    }
}