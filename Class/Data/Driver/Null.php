<?php

/**
 * Заглушка для драйвера источника данных
 *
 * @author goorus, morph
 */
class Data_Driver_Null extends Data_Driver_Abstract
{
    /**
     * @inheritdoc
     */
    public function executeCommand(Query_Abstract $query,
        Query_Options $options)
    {
        $from = $query->getPart(Query::FROM);
        $modelName = reset($from);
        if ($query->type() == Query::DELETE) {
            $from = $query->getPart(Query::FROM);
            $modelName = reset($from)[Query::TABLE];
        }
        $locator = IcEngine::serviceLocator();
        $modelScheme = $locator->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataDriver = $dataSource->getDataDriver();
        $dataDriver->setTouchedRows(1);
        return array();
    }
}