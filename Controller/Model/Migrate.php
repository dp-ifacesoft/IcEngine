<?php

/**
 * Контроллер для миграции моделей на основе автоматических миграций, созданных
 * Model_Diff
 * 
 * @author morph
 */
class Controller_Model_Migrate extends Controller_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'category'  => 'modelDiff'
    );
    
    /**
     * Выполнить миграцию для всех моделей
     * 
     * @Context("controllerManager", "helperArray", "helperMigrationQueue")
     * @Template(null)
     * @Validator("User_Cli")
     * @ConfigMerge("Controller_Model_Diff")
     */
    public function all($context, $mark = false)
    {
        $config = $this->config();
        $queue = $context->helperMigrationQueue->getQueue($config->category);
        $modelNames = array_unique(
            $context->helperArray->column($queue, 'modelName')
        );
        $input = array('mark' => $mark);
        foreach ($modelNames as $modelName) {
            $input['name'] = $modelName;
            $context->controllerManager->call('Model_Migrate', 'index', $input);
        }
    }
    
    /**
     * Запуск автоматических миграций для заданной модели
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("migrationManager", "helperMigrationQueue", "configManager")
     * @Context("controllerManager", "helperMigrationMark")
     * @Context("helperMigrationModel")
     * @ConfigMerge("Controller_Model_Diff")
     */
    public function index($name, $context, $mark = false)
    {
        $config = $this->config();
        echo 'Preparing migrations for model "' . $name . '"...';
        $queue = $context->helperMigrationQueue->getQueue($config->category);
        echo ' done.' . PHP_EOL;
        if (!$queue) {
            echo 'No actual migrations for model "' . $name . '".' . PHP_EOL;
        }
        $dataSourceName = $context->dds->getDataSource()->getName();
        foreach ($queue as $queueData) {
            $migrationName = $queueData['name'];
            $migration = $context->migrationManager->get($migrationName);
            if ($queueData['modelName'] != $name || $queueData['isFinished']) {
                continue;
            }
            $marks = $context->helperMigrationModel->getMarks($migrationName);
            if ($queueData['isMarked'] || in_array($dataSourceName, $marks)) {
                $migration->log('up');
                continue;
            }
            echo 'Applying "' . $migration->getName() . "...";
            $migration->up();
            echo ' done.' . PHP_EOL;
            $migration->log('up');
            if ($mark) {
                $context->helperMigrationMark->mark($migrationName);
                $context->helperMigrationModel->mark(
                    $migrationName, $dataSourceName
                );
            }
        }
    }
}