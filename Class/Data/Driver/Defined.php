<?php

/**
 * Драйвер для работы с моделями типа "Defined"
 * 
 * @author morph
 */
class Data_Driver_Defined extends Data_Driver_Abstract
{
    /**
     * @inheritdoc
     */
    public function executeCommand(Query_Abstract $query, 
        Query_Options $options) 
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperArray = $serviceLocator->getService('helperArray');
        $from = $query->getPart(Query::FROM);
        if (!$from) {
            return array();
        }
        $modelName = reset($from)[Query::TABLE];
        $rows = $modelName::$rows;
        if (!$rows) {
            $config = $serviceLocator->getService('configManager')
                ->get($modelName);
            if ($config && $config->rows) {
                $rows = $config->rows->__toArray();
            }
        }
        $where = $query->getPart(Query::WHERE); 
        if ($where) {
            $criteria = array();
            foreach ($where as $part) {
                $where = $part[Query::WHERE];
                if (strpos($where, '.') !== false) {
                    list(,$quotedfield) = explode('.', $where);
                    $field = trim($quotedfield, '`');
                } else {
                    $field = trim($where, '`');
                }
                $criteria[$field] = $part[Query::VALUE];
            }
            $rows = $helperArray->filter($rows, $criteria);
        }
        $select = $query->getPart(Query::SELECT);
        $keys = array_keys($select);
        $keysExploded = array();
        foreach ($keys as $key) {
            if (strpos($key, ',') === false) {
                $keysExploded[] = $key;
                continue;
            }
            $exploded = explode(',', $key);
            foreach ($exploded as $item) {
                $keysExploded[] = trim($item);
            }
        }
        $resultKeys = array_unique($keysExploded);
        if (strpos($resultKeys[0], '*') !== false) {
            return $rows;
        }
        $result = $helperArray->column($rows, $resultKeys);
        if (count($resultKeys) > 1) {
            return $result;
        }
        foreach ($result as $i => $row) {
            $result[$i] = array($resultKeys[0] => $row);
        }
        return $result;
    }
}