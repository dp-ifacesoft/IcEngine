<?php

/**
 * Главный помошник программиста
 */
class Controller_Admin_Action extends Controller_Abstract
{
    /**
     * @Template(null)
     */
    public function index($table, $rowId, $context)
    {
        $query = $context->queryBuilder
            ->delete()
            ->from($table);
        if ($rowId) {
            $keyField = $this->getService('modelScheme')->keyField($table);
            $query->where($keyField, $rowId);
        }
        $context->dds->execute($query);
    }
}