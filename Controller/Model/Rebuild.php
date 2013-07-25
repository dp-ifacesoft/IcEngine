<?php

/**
 * Пересобрать модель из источника данных
 * 
 * @author morph
 */
class Controller_Model_Rebuild extends Controller_Abstract
{
    /**
     * Пересобрать модель
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelMigrateRebuild")
     */
    public function index($name, $context)
    {
        echo 'Model "' . $name . '" rebuilding...';
        $context->helperModelMigrateRebuild->rebuild($name);
        echo ' done' . PHP_EOL;
    }
}