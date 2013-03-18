<?php

/**
 * Мэппер для работы с моделями типа "Defined"
 * 
 * @author morph
 */
class Data_Mapper_Defined extends Data_Mapper_Abstract
{
    /**
     * @inheritdoc
     */
    public function _execute(Query_Abstract $query, $options = null) 
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperArray = $serviceLocator->getService('helperArray');
        $helperString = $serviceLocator->getService('helperString');
        $from = $query->getPart(Query::FROM);
        if (!$from) {
            return array();
        }
        $modelName = reset($from)[Query::TABLE];
        $rows = $modelName::$rows;
        $where = $query->getPart(Query::WHERE);
        if ($where) {
            $criteria = array();
            foreach ($where as $part) {
                $where = $part[Query::WHERE];
                if (strpos($where, '.') !== false) {
                    list($table, $quotedfield) = explode('.', $where);
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