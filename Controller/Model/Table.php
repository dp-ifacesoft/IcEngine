<?php

/**
 * Создание таблиц по схеме для моделей с источником данных Mysql
 * 
 * @author morph
 */
class Controller_Model_Table extends Controller_Abstract
{
    /**
     * Создать таблицу
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelTable")
     */
    public function create($name, $context)
    {
        $context->helperModelTable->create($name);
    }
}