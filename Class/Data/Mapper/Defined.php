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
        if (strpos($keys[0], '*') !== false) {
            return $rows;
        }
        return $helperArray->column($rows, $keys);
    }
}