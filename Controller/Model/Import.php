<?php

/**
 * Инпорт схемы модели из СУБД. Создание файлов модели и схемы
 *
 * @author morph
 */
class Controller_Model_Import extends Controller_Abstract
{
    /**
     * Начать инпорт модели
     *
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelImport")
     */
    public function index($name, $context)
    {
        echo 'Model "' . $name . '" importing...';
        $context->helperModelImport->import($name);
        echo ' done' . PHP_EOL;
    }
}