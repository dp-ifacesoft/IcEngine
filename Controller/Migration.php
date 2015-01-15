<?php

/**
 * Контроллер для запуска миграций
 *
 * @author morph, neon
 */
class Controller_Migration extends Controller_Abstract
{
	/**
	 * Применить конкретную миграцию
	 *
	 * @Template(null)
     * @Validator("User_Cli")
     * @Context("migrationManager")
	 */
	public function apply($name, $action, $context)
	{
        $migration = $context->migrationManager->get($name);
        if (!$migration) {
            return;
        }
        $migration->setParams($this->input->receiveAll());
		$result = call_user_func(array($migration, $action));
		if ($result) {
			echo 'Migration done' . PHP_EOL;
		}
        $migration->log($action);
        $dataSourceManager = $this->getService('dataSourceManager');
        $defaultDataSource = $dataSourceManager->get('default');
        $driver = $defaultDataSource->getDataDriver();
        $driver->clearCache();
        echo 'Mapper clear' . PHP_EOL;
	}

	/**
	 * Создать миграцию
	 *
	 * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperMigration")
	 */
	public function create($name, $category, $context)
	{
        if (!isset($name)) {
            echo 'имя миграции name не задано. Миграция не создана.' . PHP_EOL;
            return;
        }
        try {
            $context->helperMigration->create($name, $category);
        } catch (Exception $exeption) {
            echo 'Мирграция не создана. ' . $exeption->getMessage() . PHP_EOL;
            return;
        }
        echo 'Мирграция создана. ' . PHP_EOL;
	}

	/**
	 * Узнать текущую миграцию
     *
     * @Template(null)
     * @Validator("User_Cli")
     * @Content("helperMigrationQueue")
	 */
	public function current($category, $context)
	{
        print_r($context->MigrationQueue->current($category));
	}

	/**
	 * Откатить миграцию
	 *
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperMigrationQueue", "helperMigrationProcess")
	 */
	public function down($to, $category, $context)
	{
        if (!$context->helperMigrationProcess->validDown($to, $category)) {
            return;
        }
        $context->helperMigrationProcess->down($to, $category);
	}

	/**
	 * Получить очередь миграций по категории
     *
     * @Template(null)
     * @Validator("User_Cli")
     * @Content("helperMigrationQueue")
	 */
	public function queue($category, $context)
	{
        print_r($context->helperMigrationQueue->getQueue($category));
	}

	/**
	 * Поднять миграцию
	 *
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperMigrationProcess")
	 */
	public function up($to, $category, $context)
	{
        if (!$context->helperMigrationProcess->validUp($to, $category)) {
            return;
        }
        $context->helperMigrationProcess->up($to, $category);
	}
    
    /**
     * вывести список миграций за последние $period дней
     * @param string $period strtotime разбег
     * @Template(null)
     * @Context("helperMigration", "helperFile")
     */
    public function show($context, $period = '-1 Months')
    {
        $list = [];
        $filesPaths = $context->helperFile->getFileList('Ice/Model/Migration/');
        $datePattern = '#\*.*?Created\sat:\s(\d{4}-\d{2}-\d{2})#';
        $authorPattern = '#\*.*?Created\sat:\s(\d{4}-\d{2}-\d{2})#';
        foreach ($filesPaths as $filePath) {
            $fileContent = file_get_contents($filePath);
            preg_match_all($datePattern, $fileContent, $dateMatches);
            if(isset($dateMatches[1][0])){
                if(strtotime($dateMatches[1][0]) >=  strtotime($period)) {
                    $list[] = basename($filePath);
                }
            }
        }
        var_dump($list);
    }
}