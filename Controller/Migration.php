<?php

/**
 * @desc Контроллер для запуска миграций
 * @author Илья Колесников
 */
class Controller_Migration extends Controller_Abstract
{
	/**
	 * @desc Применить конкретную миграцию
	 * @param string $name
	 * @param string $action
	 */
	public function apply ($name, $action)
	{
//		if (User::id () >= 0)
//		{
//			echo 'Access denied' . PHP_EOL;
//			return;
//		}

		$args = $this->_input->receiveAll ();
		if (!isset ($args ['name']))
		{
			return;
		}
		unset ($args ['name']);
		if (!isset ($args ['action']))
		{
			return;
		}
		unset ($args ['action']);
		$base = 'default';
		if (isset ($args ['base']))
		{
			$base = $args ['base'];
			unset ($args ['base']);
		}
		Loader::load ('Helper_Migration');
		$migration = Helper_Migration::byName ($name);
		if (!$migration)
		{
			return;
		}
		$queue = Helper_Migration::getQueue ($base);
		$params = array ();
		if (isset ($queue [$name]))
		{
			$params = $queue [$name];
		}
		$params = array_merge ($params, (array) $args);
		$migration->setParams ($params);
		if ($migration->$action ())
		{
			if (!empty ($migration->model))
			{
				$table = Model_Scheme::table ($migration->model);
				Controller_Manager::call (
					'Model', 'fromTable',
					array (
						'name'		=> $table,
						'rewrite'	=> 1
					)
				);
			}
			echo 'Migration done' . PHP_EOL;
		}
		Helper_Migration::log ($name, $action);
		Helper_Migration::setLastData ($name, $action, $base);
		Helper_Migration::logFlush ();
	}

	/**
	 * @desc Создать миграцию
	 * @param string $name
	 */
	public function create ($name, $desc, $author, $base)
	{
		$filename = IcEngine::root () . 'Ice/Model/Migration/' . $name . '.php';
		if (is_file ($filename))
		{
			return;
		}
		$task = Controller_Manager::call (
			'Migration', 'seq',
			array ()
		);
		$seq = 'undefined';
		if ($task)
		{
			$buffer = $task->getTransaction ()->buffer ();
			if (isset ($buffer ['seq']))
			{
				$seq = $buffer ['seq'];
			}
		}
		Loader::load ('Helper_Code_Generator');
		$output = Helper_Code_Generator::fromTemplate (
			'migration',
			array (
				'desc'		=> $desc,
				'date'		=> Helper_Date::toUnix (),
				'author'	=> $author,
				'base'		=> $base,
				'seq'		=> $seq,
				'name'		=> $name
			)
		);
		echo 'File: ' . $filename . PHP_EOL;
		file_put_contents ($filename, $output);

	}

	/**
	 * @desc Узнать текущую миграцию
	 */
	public function current ($base)
	{
		if (User::id () >= 0)
		{
			echo 'Access denied' . PHP_EOL;
			return;
		}

		Loader::load ('Helper_Migration');
		$last_data = Helper_Migration::getLastData ();
		print_r ($last_data);
	}

	/**
	 * @desc Откатить миграцию
	 * @param string $to
	 * @param string $base
	 */
	public function down ($to, $base = 'default')
	{
		if (User::id () >= 0)
		{
			echo 'Access denied' . PHP_EOL;
			return;
		}

		$args = $this->_input->receiveAll ();
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
		Loader::load ('Helper_Migration');
		Helper_Migration::migration ($to, 0, $args, $base);
	}

	/**
	 * @desc Поднимает до последней по списку миграции
	 * @param string $base
	 * @param string $action
	 */
	public function last ($base, $action = 'up')
	{
		Loader::load ('Helper_Migration');
		$queue = Helper_Migration::getQueue ($base);
		$last = end ($queue);
		if (is_array ($last))
		{
			$last = key ($last);
		}
		Controller_Manager::call (
			'Migration', $action,
			array (
				'name'	=> $last
			)
		);
	}

	/**
	 * @desc Получить список миграций
	 * @param string $base - база
	 */
	public function queue ($base = 'default')
	{
		Loader::load ('Helper_Migration');
		print_r (Helper_Migration::getQueue ($base));
	}

	/**
	 * @desc Востановить данные миграции
	 * @param string $name
	 */
	public function restore ($name)
	{
		Loader::load ('Helper_Migration');
		Helper_Migration::restore ($name);
	}

	/**
	 * @desc Получить уникальный номер миграции
	 */
	public function seq ()
	{
		$this->_task->setTemplate (null);
		$url = Helper_Site_Location::get ('seq_url');
		if (!$url)
		{
			return;
		}
		$seq = file_get_contents ($url);
		echo 'Migration #' . $seq . PHP_EOL;
		$this->_output->send (array (
			'seq'	=> $seq
		));
	}

	/**
	 * @desc сформировать уникальный номер миграции
	 */
	public function seqGet ()
	{
		$this->_task->setTemplate (null);
		$filename = IcEngine::root () . 'Ice/Var/Helper/Migration/seq';
		$current = 0;
		if (file_exists ($filename))
		{
			$current = (int) file_get_contents ($filename);
		}
		$current++;
		file_put_contents ($filename, $current);
		$current = str_pad ($current, 8 - strlen ($current), '0', STR_PAD_LEFT);
		echo $current;
	}

	/**
	 * @desc Поднять миграцию
	 * @param string $to
	 * @param string $base
	 */
	public function up ($to, $base = 'default')
	{
		if (User::id () >= 0)
		{
			echo 'Access denied' . PHP_EOL;
			return;
		}

		$args = $this->_input->receiveAll ();
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
		Loader::load ('Helper_Migration');
		Helper_Migration::migration ($to, 1, $args, $base);
	}
}