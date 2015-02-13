<?php

/**
 * Хелпер для Model_Option
 * 
 * @author morph
 * @Service("helperModelOption")
 */
class Helper_Model_Option extends Helper_Abstract
{
    /**
     * Хелпер генерации кода
     * 
     * @Inject("helperCodeGenerator")
     * @var Helper_Code_Generator
     */
    protected $helperCodeGenerator;
    
    /**
     * Создать Model_Option
     * 
     * @param string $modelName
     * @param string $optionName
     */
    public function create($modelName, $optionName)
    {
        $filename = IcEngine::root() . 'Ice/Model/' . 
            str_replace('_', '/', $modelName) . '/Option/' .
            str_replace('_', '/', $optionName) . '.php';
        $output = $this->helperCodeGenerator->fromTemplate(
            'modelOption', array(
                'name'      => $optionName,
                'modelName' => $modelName
            )
        );
        file_put_contents($filename, $output);
    }
    
    /**
     * Изменить хелпер генерации кода
     * 
     * @param Helper_Code_Generator $helperCodeGenerator
     */
    public function setHelperCodeGenerator($helperCodeGenerator)
    {
        $this->helperCodeGenerator = $helperCodeGenerator;
    }
}