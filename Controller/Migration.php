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
        $context->helperMigration->create($name, $category);
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
}