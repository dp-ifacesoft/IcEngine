<?php

/**
 * Контроллер для работы с Model_Option
 * 
 * @author morph
 */
class Controller_Model_Option extends Controller_Abstract
{
    /**
     * Создание нового пустого Model_Option
     * 
     * @Validator("User_Cli")
     * @Template(null)
     * @Context("helperModelOption")
     */
    public function create($name, $modelName, $context)
    {
        $context->helperModelOption->create($modelName, $name);
    }
}