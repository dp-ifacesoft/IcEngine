<?php

/**
 * Помощник модели
 * 
 * @author morph
 * @Service("helperModel")
 */
class Helper_Model
{
    /**
     * Получить public поля подели
     * 
     * @param Model $model
     * @return array
     */
    public function getVars($model)
    {
        return get_object_vars($model);
    }
}