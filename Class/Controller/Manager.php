<?php
/**
 *
 * @desc Менеджер контроллеров.
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Manager extends Manager_Abstract
{

	/**
	 * @desc Загруженные контроллеры.
	 * @var array
	 */
	protected static $_controllers = array ();

	/**
	 * @desc Стек входных транспортов контроллеров.
	 * @var array
	 */
	protected static $_controllersInputs = array ();

	/**
	 * @desc Стек выходных транспортов контроллеров.
	 * @var array
	 */
	protected static $_controllersOutputs = array ();

	/**
	 * @desc Текущее задание
	 * @var Controller_Task
	 */
	protected static $_currentTask;

	/**
	 * @desc Транспорт входных данных.
	 * @var Data_Transport
	 */
	protected static $_input;

	/**
	 * @desc Транспорт выходных данных.
	 * @var Data_Transport
	 */
	protected static $_output;

	/**
	 * @desc Отложенные очереди заданийs
	 * @var array <array>
	 */
	protected static $_tasksBuffer = array ();

	/**
	 * @desc Очередь заданий.
	 * @var array <Router_Action>
	 */
	protected static $_tasksQueue = array ();

	/**
	 * @desc Результаты выполнения очереди
	 * @var array <Controller_Task>
	 */
	protected static $_tasksResults = array ();

	/**
	 * @desc Буффер результатов
	 * @var array <array <Controller_Task>>
	 */
	protected static $_tasksResultsBuffer = array ();

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Фильтры для выходных данных
		 * @var array
		 */
		'output_filters'	=> array (),
		/**
		 * @desc Настройки кэширования для экшенов.
		 * @var array
		 */
		'actions'			=> array ()
	);

	/**
	 * @desc Настройки кэширования для контроллера-экшена.
	 * @param string $controller Контроллер
	 * @param string $action Экшен
	 * @return Objective
	 */
	protected static function _cacheConfig ($controller, $action)
	{
		$config = self::config ();
		$cfg = $config ['actions'][$controller . '::' . $action];
		$cfg = $cfg ? $cfg : $config ['actions'] [$controller];

		if (isset ($cfg ['cache_config']))
		{
			list ($class_name, $method) = explode ('::', $cfg ['cache_config']);
			return call_user_func_array (
				array ($class_name, $method),
				array ($cfg)
			);
		}

		if (isset ($cfg ['tags'], $cfg ['tag_provider']))
		{
			$provider = Data_Provider_Manager::get ($cfg ['tag_provider']);

			if ($provider)
			{
				$tags = $provider->getTags ($cfg ['tags']->__toArray ());

				if ($tags)
				{
					$cfg ['current_tags'] = $tags;
				}
			}
		}

		return $cfg;
	}

	/**
	 *
	 * @param Controller_Abstract $controller
	 */
	public static function beforeAction ($controller)
	{
		self::$_controllersInputs [] = $controller->getInput ();
		self::$_controllersOutputs [] = $controller->getOutput ();

		self::getOutput ()->beginTransaction ();

		$controller
			->setInput (self::getInput ())
			->setOutput (self::getOutput ());
	}

	/**
	 * @desc Вызов экшена контроллера.
	 * @param string $name Название контроллера.
	 * @param string $method Метод.
	 * @param array|Data_Transport $input Входные данные.
	 * @param Controller_Task $task [optional] Задание
	 * @return Controller_Task
	 */
	public static function call ($name, $method, $input = array(),
		$task = null, $notLog = false)
	{
		return self::callUncached ($name, $method, $input, $task, $notLog);
	}

	/**
	 * @desc Вызов экшена без кэширования.
	 * @param string $name Название контроллера.
	 * @param string $method Метод.
	 * @param array|Data_Transport $input Входные данные.
	 * @param Controller_Task $task [optional] Итерация
	 * диспетчера.
	 * @return Controller_Task Итерация с результатами.
	 */
	public static function callUncached ($name, $method, $input, $task = null,
		$notLog = false)
	{
		if (Tracer::$enabled && !$notLog) {
			Tracer::resetDeltaModelCount();
			Tracer::resetDeltaQueryCount();
			Tracer::begin(__CLASS__, __METHOD__, __LINE__, $name, $method);
		}

		if (!$task)
		{
			$task = new Controller_Task (
				new Controller_Action (array (
					'id'			=> null,
					'controller'	=> $name,
					'action'		=> $method
				))
			);
		}

		$controller = self::get ($name);

		$temp_input = $controller->getInput ();
		$temp_output = $controller->getOutput ();
		$temp_task = $controller->getTask ();

		if (is_null ($input))
		{
			$controller->setInput (self::getInput ());
		}
		elseif (is_array ($input))
		{
			$tmp = new Data_Transport ();
			$tmp->beginTransaction ()->send ($input);
			$controller->setInput ($tmp);
		}
		else
		{
			$controller->setInput ($input);
		}

		$controller
			->setOutput (self::getOutput ())
			->setTask ($task);

		$controller->getOutput ()->beginTransaction ();

		//$controller->_beforeAction ($method);

		// _beforeAction не генерировал ошибки, можно продолжать
        if (!$controller->hasErrors ())
        {
			$reflection = new ReflectionMethod ($controller, $method);

			$params = $reflection->getParameters ();
			$c_input = $controller->getInput ();
			if ($params)
			{
				foreach ($params as &$param)
				{
					$param_value = $c_input->receive ($param->name);
					if (!$param_value)
					{
						$reflection_param = new ReflectionParameter (
							array ($controller, $method),
							$param->name
						);

						if ($reflection_param && $reflection_param->isOptional ())
						{
							$param_value = $reflection_param->getDefaultValue ();
							if ($c_input && $c_input->getProvider(0)) {
								$c_input->getProvider(0)->set(
									$param->name,
									$param_value
								);
							}
						}
					}
					$param = $param_value;
				}
			}

			call_user_func_array (
				array ($controller, $method),
				$params ? $params : array ()
			);

			//$controller->_afterAction ($method);
		}

		$task->setTransaction ($controller->getOutput ()->endTransaction ());

		$controller
			->setInput ($temp_input)
			->setOutput ($temp_output)
			->setTask ($temp_task);

		if (Tracer::$enabled && !$notLog) {
			$deltaModelCount = Tracer::getDeltaModelCount();
			$deltaQueryCount = Tracer::getDeltaQueryCount();
			Tracer::incControllerCount();
			Tracer::end($deltaModelCount, $deltaQueryCount,
				memory_get_usage(), 0);
		}

		return $task;
	}

	/**
	 * @desc Создаем задания из экшинов
	 * @param Route_Action_Collection $actions
	 * @param Data_Transport $input
	 * @return array <Controller_Task>
	 */
	public static function createTasks (Route_Action_Collection $actions,
		Data_Transport $input)
	{
		$tasks = array ();

		foreach ($actions as $action)
		{
			$task = new Controller_Task ($action);
			$task->setInput ($input);

			$tasks [] = $task;
		}

		return $tasks;
	}

	/**
	 * @desc Очистка результатов работы контроллеров.
	 */
	public static function flushResults ()
	{
		self::$_tasksResults = array ();
	}

	/**
	 * @desc Возвращает контроллер по названию.
	 * @param string $controller_name
	 * @return Controller_Abstract
	 */
	public static function get ($controller_name)
	{
		$class_name = 'Controller_' . $controller_name;
		$controller = Resource_Manager::get (
			'Controller',
			$class_name
		);

		if (!($controller instanceof Controller_Abstract))
		{
			$file = str_replace ('_', '/', $controller_name) . '.php';

			if (!Loader::requireOnce ($file, 'Controller'))
			{
				throw new Controller_Exception ("Controller $class_name not found.");
			}

			$controller = new $class_name;

			Resource_Manager::set (
				'Controller',
				$class_name,
				$controller
			);
		}
		return $controller;
	}

	/**
	 * @return Data_Transport
	 */
	public static function getInput ()
	{
		if (!self::$_input)
		{
			self::$_input  = new Data_Transport ();
		}
		return self::$_input;
	}

	/**
	 * @desc Возвращает транспорт для выходных данных по умолчанию.
	 * @return Data_Transport
	 */
	public static function getOutput ()
	{
		if (!self::$_output)
		{
			self::$_output = new Data_Transport ();

			foreach (self::config ()->output_filters as $filter)
			{
				$filter_class = 'Filter_' . $filter;
				$filter = new $filter_class ();
				self::$_output->outputFilters ()->append ($filter);
			}
		}
		return self::$_output;
	}

	/**
	 * @desc Выполняет указанный контроллер, экшен с заданными параметрами.
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры контроллера.
	 * @param mixed $options=true Параметры вызова.
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public static function html ($action, array $args = array (),
		$options = true)
	{
		$a = explode ('/', $action);
		if (count ($a) == 1)
		{
			$a [1] = 'index';
		}

		$cache_config = self::_cacheConfig ($a [0], $a [1]);

		if (is_bool ($options))
		{
			$options = array ('full_result' => !$options);
		}

		$html = Executor::execute (
			array (__CLASS__, 'htmlUncached'),
			array ($a, $args, $options),
			$cache_config
		);

		return $html;
	}

	/**
	 * @desc Выполняет указанный контроллер, экшен с заданными параметрами,
	 * не используется кэширование.
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры контроллера.
	 * @param mixed $options=true Параметры вызова.
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public static function htmlUncached ($action, array $args = array (),
		$options = true)
	{
		$a = is_array ($action) ? $action : explode ('/', $action);

		if (count ($a) == 1)
		{
			$a [1] = 'index';
		}

		if (Tracer::$enabled) {
			Tracer::resetDeltaModelCount();
			Tracer::resetDeltaQueryCount();
			Tracer::begin(__CLASS__, __METHOD__, __LINE__, $a[0], $a[1]);
		}

		$iteration = self::call ($a [0], $a [1], $args, null, true);

		$buffer = $iteration->getTransaction ()->buffer ();
		$result = array (
			'error'		=> isset ($buffer ['error']) ? $buffer ['error'] : '',
			'data'		=>
				isset ($buffer ['data']) ?
				$buffer ['data'] :
				array (),
			'html'		=> null
		);

		$tpl = $iteration->getTemplate ();

		if ($tpl)
		{
			$view = View_Render_Manager::pushViewByName ('Smarty');

			try
			{
				$view->assign ($buffer);
				if (Tracer::$enabled) {
					$startTime = microtime(true);
				}
				$result ['html'] = $view->fetch ($tpl);
				if (Tracer::$enabled) {
					$endTime = microtime(true);
				}
			}
			catch (Exception $e)
			{
				$msg =
					'[' . $e->getFile () . '@' .
					$e->getLine () . ':' .
					$e->getCode () . '] ' .
					$e->getMessage () . PHP_EOL;

				error_log (
					$msg . PHP_EOL .
					$e->getTraceAsString () . PHP_EOL,
					E_USER_ERROR, 3
				);

				Debug::log ($msg);

				$result ['error'] = 'Controller_Manager: Error in template.';
			}

			View_Render_Manager::popView ();
		}

		if (Tracer::$enabled) {
			$deltaModelCount = Tracer::getDeltaModelCount();
			$deltaQueryCount = Tracer::getDeltaQueryCount();
			$delta = $endTime - $startTime;
			Tracer::incRenderTime($delta);
			Tracer::incControllerCount();
			Tracer::end($deltaModelCount, $deltaQueryCount, memory_get_usage(),
				$delta);
		}

		if (!empty ($options ['with_buffer']))
		{
			$options = array ('full_result' => true);
			$result ['buffer'] = $buffer;
		}

		if ($options === true)
		{
			$options = array ('full_result' => false);
		}
		elseif ($options === false)
		{
			$options = array ('full_result' => true);
		}

		return isset ($options ['full_result']) && $options ['full_result']
			? $result
			: $result ['html'];
	}

	/**
	 * @desc Добавление задания в текущую очередь выполнения.
	 * @param mixed $action
	 */
	public static function pushTasks ($action)
	{
		if (
			$action instanceof Route_Action_Collection ||
			$action instanceof Controller_Action_Collection
		)
		{
			foreach ($action as $resource)
			{
				self::$_tasksQueue [] =
					new Controller_Task ($resource);
			}
		}
		elseif (
			$action instanceof Controller_Action ||
			$action instanceof Route_Action
		)
		{
			self::$_tasksQueue [] = new Controller_Task ($action);
		}
		elseif (is_array ($action))
		{
			if (isset ($action ['controller']))
			{
				$action = func_get_args ();
			}

			foreach ($action as $info)
			{
				self::pushTasks (new Controller_Action (array (
					'controller'	=> $info ['controller'],
					'action'		=> $info ['action']
				)));
			}
		}
		else
		{
			throw new Zend_Exception ('Illegal type.');
		}
	}

	/**
	 *
	 * @param Controller_Task|Route_Action|Controller_Action $action
	 * @return Controller_Task
	 */
	public static function run ($task)
	{
		$parent_task = self::$_currentTask;

		self::$_currentTask = $task;

		$action = $task->controllerAction ();

		$task = self::call (
			$action->controller,
			$action->action,
			$task->getInput (),
			$task
		);

		self::$_currentTask = $parent_task;

		return $task;
	}

	/**
	 * @desc Выполнение очереди заданий
	 * @param array $actions
	 * @return array
	 */
	public static function runTasks ($tasks)
	{
		self::$_tasksBuffer [] = self::$_tasksQueue;
		self::$_tasksResultsBuffer [] = self::$_tasksResults;

		self::$_tasksQueue = $tasks;

		self::$_tasksResults = array ();

		for ($i = 0; $i < count (self::$_tasksQueue); ++$i)
		{
			$task = self::run (self::$_tasksQueue [$i]);
			if (!$task->getIgnore ())
			{
				self::$_tasksResults [] = $task;
			}
		}

		$result = self::$_tasksResults;
		self::$_tasksQueue = array_pop (self::$_tasksBuffer);
		self::$_tasksResults = array_pop (self::$_tasksResultsBuffer);

		return $result;
	}

}
