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
}