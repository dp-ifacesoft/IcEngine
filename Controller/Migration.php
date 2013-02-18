<?php

/**
 * Контроллер для запуска миграций
 *
 * @author Илья Колесников, neon
 */
class Controller_Migration extends Controller_Abstract
{
	/**
	 * Применить конкретную миграцию
	 *
	 * @param string $name
	 * @param string $action
	 */
	public function apply($name, $action)
	{
		$helperMigration = $this->getService('helperMigration');
		$modelScheme = $this->getService('modelScheme');
		$controllerManager = $this->getService('controllerManager');
		$args = $this->input->receiveAll();
		if (!isset($args['name'])) {
			return;
		}
		unset($args['name']);
		if (!isset($args['action'])) {
			return;
		}
		unset($args['action']);
		$base = 'default';
		if (isset($args['base'])) {
			$base = $args['base'];
			unset($args['base']);
		}
		$migration = $helperMigration->byName($name);
		if (!$migration) {
			return;
		}
		$queue = $helperMigration->getQueue($base);
		$params = array();
		if (isset($queue[$name])) {
			$params = $queue [$name];
		}
		$params = array_merge($params, (array) $args);
		$migration->setParams($params);
		if ($migration->$action()) {
			if (!empty($migration->model)) {
				$table = $modelScheme->table($migration->model);
				$controllerManager->call(
					'Model', 'fromTable',
					array(
						'name'		=> $table,
						'rewrite'	=> 1
					)
				);
			}
			echo 'Migration done' . PHP_EOL;
		}
		$helperMigration->log($name, $action);
		$helperMigration->setLastData($name, $action, $base);
		$helperMigration->logFlush();
	}

	/**
	 * Создать миграцию
	 *
	 * @param string $name
	 */
	public function create($name, $desc, $author, $base)
	{
		$controllerManager = $this->getService('controllerManager');
		$helperCodeGenerator = $this->getService('helperCodeGenerator');
		$helperDate = $this->getService('helperDate');
		$filename = IcEngine::root() . 'Ice/Model/Migration/' . $name . '.php';
		if (is_file($filename)) {
			return;
		}
		$task = $controllerManager->call(
			'Migration', 'seq',
			array()
		);
		$seq = 'undefined';
		if ($task) {
			$buffer = $task->getTransaction()->buffer();
			if (isset($buffer['seq'])) {
				$seq = $buffer['seq'];
			}
		}
		$output = $helperCodeGenerator->fromTemplate(
			'migration',
			array(
				'desc'		=> $desc,
				'date'		=> $helperDate->toUnix(),
				'author'	=> $author,
				'base'		=> $base,
				'seq'		=> $seq,
				'name'		=> $name
			)
		);
		echo 'File: ' . $filename . PHP_EOL;
		file_put_contents($filename, $output);
	}

	/**
	 * @desc Узнать текущую миграцию
	 */
	public function current($base)
	{
		$userService = $this->getService('user');
        $helperMigration = $this->getService('helperMigration');
        if ($userService->id() >= 0)
		{
			echo 'Access denied' . PHP_EOL;
			return;
		}

		$last_data = $helperMigration->getLastData();
		print_r ($last_data);
	}

	/**
	 * @desc Откатить миграцию
	 * @param string $to
	 * @param string $base
	 */
	public function down($to, $base = 'default')
	{
		$userService = $this->getService('user');
        $helperMigration = $this->getService('helperMigration');
        if ($userService->id() >= 0)
		{
			echo 'Access denied' . PHP_EOL;
			return;
		}

		$args = $this->input->receiveAll();
		if (!isset ($args ['to']))
		{
			return;
		}
		unset ($args ['to']);
		if (isset ($args ['base']))
		{
			$base = $args ['base'];
			unset ($args ['base']);
		}
		$helperMigration->migration($to, 0, $args, $base);
	}

	/**
	 * @desc Поднимает до последней по списку миграции
	 * @param string $base
	 * @param string $action
	 */
	public function last($base, $action = 'up')
	{
        $helperMigration = $this->getService('helperMigration');
        $controllerManager = $this->getService('controllerManager');
		$queue = $helperMigration->getQueue($base);
		$last = end ($queue);
		if (is_array($last))
		{
			$last = key($last);
		}
		$controllerManager->call(
			'Migration', $action,
			array (
				'name'  => $last
			)
		);
	}

	/**
	 * @desc Получить список миграций
	 * @param string $base - база
	 */
	public function queue ($base = 'default')
	{
        $helperMigration = $this->getService('helperMigration');
		print_r($helperMigration->getQueue($base));
	}

	/**
	 * @desc Востановить данные миграции
	 * @param string $name
	 */
	public function restore ($name)
	{
        $helperMigration = $this->getService('helperMigration');
		$helperMigration->restore($name);
	}

	/**
	 * Получить уникальный номер миграции
	 */
	public function seq() {
		$this->task->setTemplate(null);
		$helperSiteLocation = $this->getService('siteLocation');
		$url = $helperSiteLocation->get('seq_url');
		if (!$url) {
			return;
		}
		$seq = file_get_contents($url);
		echo 'Migration #' . $seq . PHP_EOL;
		$this->output->send(array(
			'seq'	=> $seq
		));
	}

	/**
	 * @desc сформировать уникальный номер миграции
	 */
	public function seqGet()
	{
		$this->task->setTemplate (null);
		$filename = IcEngine::root() . 'Ice/Var/Helper/Migration/seq';
		$current = 0;
		if (file_exists($filename))
		{
			$current = (int) file_get_contents($filename);
		}
		$current++;
		file_put_contents($filename, $current);
		$current = str_pad($current, 8 - strlen($current), '0', STR_PAD_LEFT);
		echo $current;
	}

	/**
	 * @desc Поднять миграцию
	 * @param string $to
	 * @param string $base
	 */
	public function up($to, $base = 'default')
	{
        $userService = $this->getService('user');
		if ($userService->id() >= 0)
		{
			echo 'Access denied' . PHP_EOL;
			return; 
		}

		$args = $this->input->receiveAll();
		if (!isset ($args ['to']))
		{
			return;
		}
		unset ($args ['to']);
		if (isset ($args ['base']))
		{
			$base = $args ['base'];
			unset ($args ['base']);
		}
        $helperMigration = $this->getService('helperMigration');
		$helperMigration->migration($to, 1, $args, $base);
	}
}