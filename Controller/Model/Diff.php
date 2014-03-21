<?php

/**
 * Создание миграции на основании разницы в текущей схеме модели и
 * схеме этой модели в источнике данных
 * 
 * @author morph
 */
class Controller_Model_Diff extends Controller_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'category' => 'modelDiff'
    );
    
    /**
     * Создать diff-миграцию для всех измененных моделей
     * 
     * @Context("helperModelMissing")
     */
    public function all($context)
    {
        $models = $context->helperModelMissing->getClassNames();
        foreach ($models as $modelName) {
            $context->controllerManager->call('Model_Diff', 'index', array(
                'name'  => $modelName
            ));
        }
    }
    
    /**
     * Создание миграции
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelMigrateDiff", "helperMigration")
     */
    public function index($name, $context)
    {
        echo 'Creating diff-migration for model "' . $name . '"...';
        $migrations = $context->helperModelMigrateDiff->diff($name);
        if (!$migrations) {
            echo PHP_EOL . 'No changes for model "' . $name . '".' . PHP_EOL;
            return;
        }
        $content = $this->getService('helperCodeGenerator')->fromTemplate(
            'diffMigration', array(
                'migrations' => $migrations,
                'modelName'  => $name
            )
        );
        echo ' done.' . PHP_EOL;
        $migrationName = $context->helperMigration->getName(
            str_replace('_', '', $name)
        );
        echo 'Migration name: ' . $migrationName . PHP_EOL;
        $category = $this->config()->category;
        $context->helperMigration->create($migrationName, $category, array(
            'content'   => $content,
            'modelName' => $name
        ));
    }
}