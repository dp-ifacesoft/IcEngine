<?php

/**
 * Хелпер для миграций
 *
 * @Service("helperMigration")
 * @author Илья Колесников, neon
 */
class Helper_Migration
{
	/**
	 * @var Objective
	 */
	protected $config;

	/**
	 * Лог выполненных в текущей сессии миграций
	 * @var array
	 */
	protected $log;

	/**
	 * Методы миграции
	 * @var array
	 */
	public $methods = array('down', 'up');

	/**
	 * Получить имя класса
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _getClassName($content)
	{
		$matches = array();
		preg_match_all(
			'#class\s*([\w\d]+)\s*extends\s*Migration_Abstract#',
			$content,
			$matches
		);
		if (empty($matches[1][0])) {
			return;
		}
		return trim($matches[1][0]);
	}

	/**
	 * Нормализовать комментарий
	 *
	 * @param string $content
	 * @param string $tag
	 * @return string
	 */
	protected function _normalizeComment($content, $tag)
	{
		$result = '';
		$matches = array();
		preg_match_all(
			'#@' . $tag . '\s+([^@/])+#',
			$content,
			$matches
		);
		if (!empty($matches[0][0])) {
			$result = $matches[0][0];
			$result = str_replace('/**', '', $result);
			$result = str_replace('* ', '', $result);
			$result = str_replace(' *', '', $result);
			$result = str_replace('@' . $tag, '', $result);
			$result = str_replace(array("\r", "\n"), ' ', $result);
			$result = str_replace('  ', ' ', $result);
		}
		return trim($result);
	}

	/**
	 * Получить миграцию по имени
	 *
	 * @param string $name
	 * @return Migration_Abstract
	 */
	public function byName($name)
	{
		$model_name = 'Migration_' . $name;
		return new $model_name;
	}

	/**
	 * Получить конфиг хелпера
	 *
	 * @return Objective
	 */
	public function config()
	{
		$locator = IcEngine::serviceLocator();
		$configManager = $locator->getService('configManager');
		if (!$this->config) {
			$this->config = $configManager->get(
				'Migration', $this->config
			);
		}
		return $this->config;
	}

	/*
	 * Вернуть базу миграции
	 *
	 * @param string $base
	 * @return array
	 */
	public function getBase($base)
	{
		$config = $this->config();
		if (empty($config->queue) || empty($config->queue->$base)) {
			return;
		}
		return $config->queue->$base->__toArray();
	}

	/**
	 * Получить имена баз миграции
	 *
	 * @return array
	 */
	public function getBases()
	{
		$config = $this->config();
		if (empty($config->queue)) {
			return;
		}
		return array_keys($config->queue->__toArray());
	}

	/**
	 * Вернуть завершенность базы миграции
	 *
	 * @param string $base
	 * @return boolean
	 */
	public function getBaseDone($base)
	{
		$base = $this->getBase($base);
		return !empty($base['done']);
	}

	/**
	 * Получить порядковый номер миграции в очереди
	 *
	 * @param string $name
	 * @param string $base
	 */
	public function getIndex($name, $base = 'default')
	{
		$i = 0;
		$queue = $this->getQueue($base);
		if (!$queue) {
			return null;
		}
		foreach ($queue as $migration_name => $params) {
			if ($migration_name === $name || $params == $name) {
				return $i;
			}
			$i++;
		}
		return null;
	}

	/**
	 * Получить данные о последней миграции
	 *
	 * @return array
	 */
	public function getLastData()
	{
		$filename = IcEngine::root() . 'Ice/Var/Helper/Migration/last.txt';
		if (!file_exists($filename)) {
			return;
		}
		$content = file_get_contents($filename);
		if (!$content) {
			return;
		}
		return json_decode($content, true);
	}

	/**
	 * Получить данные последней миграции
	 *
	 * @return array
	 */
	public function getLastLog()
	{
		$filename = IcEngine::root() . 'Ice/Var/Helper/Migration/log.last.txt';
		if (!is_file($filename)) {
			return;
		}
		$content = file_get_contents($filename);
		if (!$content) {
			return;
		}
		return json_decode($content, true);
	}

	/**
	 * Получить список миграций, находящихся в директории миграций,
	 * но не добавленных в очередь миграций
	 *
	 * @return array
	 */
	public function getMigrations($base = 'default')
	{
		$dir = IcEngine::root() . 'Ice/Model/Migration/';
		$exec = 'find ' . $dir . '*';
		ob_start();
		system($exec);
		$content = ob_get_contents();
		ob_end_clean();
		if (!$content) {
			return;
		}
		$files = explode(PHP_EOL, $content);
		if (!$files) {
			return;
		}
		$result = array();
		$base_data = $this->getBase('base');
		$migrations = isset($base_data->migrations)
			? $base_data->migrations->__toArray()
			: array();
		foreach ($files as $file) {
			if (!is_file($file)) {
				continue;
			}
			$content = file_get_contents($file);
			$class_name = $this->_getClassName($content);
			if (!$class_name) {
				continue;
			}
			$base_name = $this->_normalizeComment($content, 'base');
			if ($base_name != $base) {
				continue;
			}
            $dateRegexp = '#Created at: (.*)#';
            $dateMatches = array();
            preg_match_all($dateRegexp, $content, $dateMatches);
            $cdate = $dateMatches[1][0];
			$comment = $this->_normalizeComment($content, 'desc');
			$author_name = $this->_normalizeComment($content, 'author');
			$seq = $this->_normalizeComment($content, 'seq');
			$params = isset($migrations[$class_name])
				? $migrations[$class_name] : null;
			$result[$class_name] = array(
				'file'		=> $file,
				'base'		=> $base_name,
				'class'		=> $class_name,
				'name'		=> substr ($class_name, strlen('Migration_')),
				'author'	=> $author_name,
				'comment'	=> $comment,
				'seq'		=> $seq,
				'params'	=> $params,
                'filectime' => $cdate,
				'filemtime'	=> date('Y-m-d H:i:s', filemtime($file))
			);
		}
		if (!$result) {
			return;
		}
		$tmp = array();
		$i = 0;
		foreach ($result as $class_name => $data) {
			$ctime = $data['filectime'];
			$tmp[$ctime] = $class_name;
		}
		ksort($tmp);
		$return = array();
		foreach ($tmp as $class_name) {
			if (!isset($result[$class_name])) {
				continue;
			}
			$return[] = $result[$class_name];
		}
		return $return;
	}

	/**
	 * Получить очередь миграций
	 *
	 * @param string $base
	 * @return array
	 */
	public function getQueue($base = 'default')
	{
		$tmp = $this->getMigrations($base);
		$result = array();
		if (!$tmp) {
			return;
		}
		foreach ($tmp as $t) {
			if (isset($t['params'])) {
				$result[$t['name']] = $t['params'];
			} else {
				$result[] = $t['name'];
			}
		}
		return $result;
	}

	/**
	 * Логировать миграцию
	 *
	 * @param string $name
	 * @param string $action
	 */
	public function log($name, $action)
	{
		$locator = IcEngine::serviceLocator();
		$helperDate = $locator->getService('helperDate');
		$this->log[] = array(
			'name'		=> $name,
			'action'	=> $action,
			'date'		=> $helperDate->toUnix()
		);
	}

	/**
	 * Записать лог в файл
	 */
	public function logFlush()
	{
		if (!$this->log) {
			return;
		}
		$locator = IcEngine::serviceLocator();
		$helperDate = $locator->getService('helperDate');
		$filename = IcEngine::root() . 'Ice/Var/Helper/Migration/log.last.txt';
		file_put_contents($filename, json_encode($this->log));
		$filename = IcEngine::root() . 'Ice/Var/Helper/Migration/log/log.' .
			$helperDate->toUnix() . '.txt';
		file_put_contents($filename, json_encode($this->log));
	}

	/**
	 * @desc Выполнить миграцию
	 * @param string $name
	 * @param integer $action
	 * @param string $base
	 */
	public function migration ($name, $action, $user_params,
		$base = 'default')
	{
		if ($this->getBaseDone ($base))
		{
			echo 'Migrations base had already done' . PHP_EOL;
			return;
		}
		$first_name = null;
		$queue = $this->getQueue ($base);
		$last_data = $this->getLastData ($base);
		if (!$last_data)
		{
			$first_name = null;
		}
		else
		{
			$first_name = $last_data ['name'];
		}
		$method = $this->methods [$action];
		if ($first_name == $name && $last_data ['action'] == $method)
		{
			echo 'No need' . PHP_EOL;
			return;
		}
		if (!$action)
		{
			$queue = array_reverse ($queue);
		}
		$st = false;
		$name_index = $this->getIndex ($name, $base);
		if (is_null ($name_index))
		{
			echo 'Migration had not found in current base' . PHP_EOL;
			return;
		}
		if ($first_name && count ($queue) > 1)
		{
			$first_index = $this->getIndex ($first_name, $base);

			if (!$first_index)
			{
				$first_name = null;
			}
			else
			{
				if ($action && $first_index > $name_index)
				{
					echo 'No need' . PHP_EOL;
					return;
				}
				elseif (!$action && $first_index < $name_index)
				{
					echo 'No need' . PHP_EOL;
					return;
				}
			}
		}

		$this->simpleRun ($this->config ()->pre, $method);

		foreach ($queue as $migration_name => $params)
		{
			if (!is_array ($params))
			{
				$migration_name = $params;
			}
			if (
				count ($queue) == 1 ||
				$migration_name == $first_name ||
				!$first_name
			)
			{
				$st = true;
			}

			if (
				$migration_name == $first_name &&
				$last_data ['action'] == $method
			)
			{
				continue;
			}

			if (!$st)
			{
				continue;
			}
			$migration = $this->byName ($migration_name);
			if (!$migration)
			{
				return $this->rollback (
					$first_name,
					$queue,
					$action,
					$user_params,
					$base
				);
			}
			if (!is_array ($params))
			{
				$params = array ();
			}
			$migration->setParams (array_merge (
				$user_params, $params
			));
			$this->storeData (
				$migration_name,
				$migration->store ()
			);
			$result = $this->run ($migration->getName (), $method, $base);
			if ($result)
			{
				echo 'Migration ' . $migration->getName () . ': done' . PHP_EOL;
			}
			else
			{
				return $this->rollback (
					$first_name,
					$queue,
					$action,
					$user_params,
					$base
				);
			}
			if ($migration_name == $name)
			{
				break;
			}
		}

		$this->simpleRun($this->config ()->post, $method);
		$this->logFlush();
		echo 'Migration done' . PHP_EOL;

		return true;
	}

	/**
	 * @desc Востановить данные миграции
	 * @param string $name
	 */
	public function restore ($name)
	{
		$migration = $this->byName ($migration_name);
		if (!$migration)
		{
			return;
		}
		$filename = IcEngine::root () . 'Ice/Var/Helper/Migration/store/' .
			$name . '.json';
		if (!file_exists ($filename))
		{
			return;
		}
		$content = file_get_contents ($filename);
		$data = json_decode ($content, true);
		$migration->restore ($data);
	}

	/**
	 * @desc Откат миграции
	 * @param string $first_name
	 * @param array $queue
	 * @param integer $action
	 * @param array $user_params
	 * @param string $base
	 * @return mixed
	 */
	public function rollback ($first_name, $queue, $action, $user_params,
		$base)
	{
		echo 'Migration error. Rollback' . PHP_EOL;
		$queue = array_reverse ($queue);
		$action = !$action;
		$method = $this->methods [$action];
		foreach ($queue as $migration_name => $params)
		{
			if (!is_array ($params))
			{
				$migration_name = $params;
			}
			$migration = $this->byName ($migration_name);
			if (!$migration)
			{
				continue;
			}
			if (!is_array ($params))
			{
				$params = array ();
			}
			$migration->setParams (array_merge (
				$user_params, $params
			));
			echo $migration->getName () . '::' . $method . PHP_EOL;
			$this->run ($migration->getName (), $method, $base);
			if ($migration_name == $first_name)
			{
				break;
			}
		}

		$this->simpleRun ($this->config ()->post, $method);

		return;
	}

	/**
	 * @desc Выполнить миграцию
	 * @param string $name
	 * @param string $method
	 * @param string $base
	 */
	public function run ($name, $method, $base)
	{
		$cmd = './ice Migration/apply --name ' . $name . ' --action ' . $method .
			' --base ' . $base;
		$host = Request::host ();
		if ($host && $host != 'default')
		{
			$cmd .= ' --host ' . $host;
		}
		ob_start ();
		system ($cmd);
		$output = ob_get_contents ();
		ob_end_clean ();
		return strpos ($output, 'Migration done') !== false;
	}

	/**
	 * Изменить данные последней миграции
	 *
	 * @param string $name
	 * @param string $action
	 * @param string $base
	 */
	public function setLastData($name, $action, $base)
	{
		$locator = IcEngine::serviceLocator();
		$helperDate = $locator->getService('helperDate');
		$data = array(
			'base'		=> $base,
			'name'		=> $name,
			'action'	=> $action,
			'date'		=> $helperDate->toUnix()
		);
		$filename = IcEngine::root() . 'Ice/Var/Helper/Migration/last.txt';
		file_put_contents($filename, json_encode($data));
	}

	public function simpleRun($list, $method)
	{
		if (!$list) {
			return;
		}
		foreach ($list as $migration_name => $params)
		{
			if (!is_array ($params))
			{
				$migration_name = $params;
			}
			$migration = $this->byName ($migration_name);
			if (!$migration)
			{
				continue;
			}
			$migration->$method ();
		}
	}

	/**
	 * @desc Сохранить данные до начала миграции
	 * @param string $name
	 * @param array $data
	 */
	public function storeData ($name, $data)
	{
		$filename = IcEngine::root () . 'Ice/Var/Helper/Migration/store/' .
			$name . '.json';
		file_put_contents ($filename, json_encode ($data));
	}
}