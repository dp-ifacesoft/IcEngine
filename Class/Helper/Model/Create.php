<?php

/**
 * Хелпер для создания модели
 *
 * @author morph
 * @Service("helperModelCreate")
 */
class Helper_Model_Create extends Helper_Abstract
{
    /**
     * Хелпер по генерации кода
     *
     * @Inject("helperCodeGenerator")
     * @Generator
     * @var Helper_Code_Generator
     */
    protected $helperCodeGenerator;

    /**
     * Создает модель
     *
     * @param Model_Create_Dto $dto
     */
    public function create($dto)
    {
        $output = $this->helperCodeGenerator->fromTemplate(
            'model', array(
                'name'          => $dto->modelName,
                'comment'       => $dto->comment,
                'date'          => $this->getService('helperDate')->toUnix(),
                'author'        => $dto->author,
                'extends'       => $dto->extends,
                'properties'    => $dto->fields
            )
        );
        $filename = $this->getFilename($dto->modelName);
        file_put_contents($filename, $output);
    }

    /**
     * Получить имя файла по модели
     *
     * @param string $modelName
     * @return string
     */
    protected function getFilename($modelName)
    {
        $parts = explode('/', str_replace('_', '/', $modelName));
        $lastName = array_pop($parts);
        $path = IcEngine::root() . 'Ice/Model' .
            ($parts ? '/' . implode('/', $parts) : '');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $filename = rtrim($path, '/') . '/' . ltrim($lastName, '/') . '.php';
        return $filename;
    }

    /**
     * Изменить хелпер по генерации кода
     *
     * @param Helper_Code_Generator $helperCodeGenerator
     */
    public function setHelperCodeGenerator($helperCodeGenerator)
    {
        $this->helperCodeGenerator = $helperCodeGenerator;
    }
    
    /**
     * Getter for "helperCodeGenerator"
     *
     * @return Helper_Code_Generator
     */
    public function getHelperCodeGenerator()
    {
        return $this->helperCodeGenerator;
    }
        
    /**
     * Setter for "helperCodeGenerator"
     *
     * @param Helper_Code_Generator helperCodeGenerator
     */
    public function setHelperModelAclConfig($helperCodeGenerator)
    {
        $this->helperCodeGenerator = $helperCodeGenerator;
    }
    
}