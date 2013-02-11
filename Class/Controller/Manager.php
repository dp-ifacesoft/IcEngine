<?php

/**
 * Менеджер контроллеров
 *
 * @author goorus, morph
 * @Service("controllerManager")
 */
class Controller_Manager extends Manager_Abstract
{
    /**
     * Акшин по умолчанию
     */
    const DEFAULT_ACTION = 'index';

    /**
     * Имя рендера по умолчанию
     */
    const DEFAULT_VIEW = 'Smarty';

    /**
     * Менеджер аннотаций
     *
     * @var Annotation_Manager_Abstract
     */
    protected $annotationManager;

    /**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * @desc Фильтры для выходных данных
		 * @var array
		 */
		'output_filters'	=> array(),

		/**
		 * @desc Настройки кэширования для экшенов.
		 * @var array
		 */
		'actions'			=> array(),

        /**
         * Контекст по умолчанию
         */
        'context'           => array(
            'queryBuilder'      => 'query',
            'modelManager'      => 'modelManager',
            'dds'               => 'dds',
            'collectionManager' => 'collectionManager',
            'configManager'     => 'configManager',
            'controllerManager' => 'controllerManager',
            'userSession'       => 'session',
            'user'              => 'user',
            'request'           => 'request'
        ),

        /**
         * Делигата менеджера контроллеров по умолчанию
         */
        'delegees'           => array(
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeParam',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeContext',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeRole',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeStatic',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeConfig',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeBefore',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeAfter',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeSlot'
        )
	);

	/**
	 * Текущее задание
     *
	 * @var Controller_Task
	 */
	protected $currentTask;

    /**
     * Менеджер провайдеров
     *
     * @Inject("dataProviderManager")
     * @var Data_Provider_Manager
     */
    protected $dataProviderManager;

    /**
     * Контекст по умолчанию
     *
     * @var Objective
     */
    protected $defaultContext;

    /**
     * "Выполнитель" по умочанию
     *
     * @var Controller_Manager_Executor_Abstract
     */
    protected $defaultExecutor;

    /**
     * Выходной транспорт по умолчанию
     *
     * @var Data_Transport
     */
    protected $defaultOutput;

    /**
     * Созданные делегита менеджера контроллеров
     *
     * @var array
     */
    protected $deleeges = array();

    /**
     * Менеджер событий
     *
     * @var Event_Manager
     */
    protected $eventManager;

    /**
     * "Выполнитель" действия контроллера
     *
     * @var Controller_Manager_Executor_Abstract
     */
    protected $executors;

	/**
	 * Транспорт входных данных
     *
	 * @var Data_Transport
	 */
	protected $input;

    /**
     * Сообщение последней ошибки
     *
     * @var string
     */
    protected $lastError;

	/**
	 * Транспорт выходных данных
     *
	 * @var array
	 */
	protected $outputs;

	/**
	 * Отложенные очереди заданий
     *
	 * @var array <array>
	 */
	protected $tasksBuffer = array();

	/**
	 * Очередь заданий
     *
	 * @var array <Router_Action>
	 */
	protected $tasksQueue = array();

	/**
	 * Результаты выполнения очереди
     *
	 * @var array <Controller_Task>
	 */
	protected $tasksResults = array();

	/**
	 * Буффер результатов
     *
	 * @var array <array <Controller_Task>>
	 */
	protected $tasksResultsBuffer = array();

    /**
     * Инжектор сервисов
     *
     * @var Service_Injector_Abstract
     */
    protected $serviceInjector;

    /**
     * Получить менеджер аннотаций
     *
     * @return Annotation_Manager_Abstract
     */
    public function annotationManager()
    {
        if (!$this->annotationManager) {
            $this->annotationManager = new Annotation_Manager_Standart();
            $provider = $this->dataProviderManager->get('Annotation');
            $annotationSource = new Annotation_Source_Standart();
            $this->annotationManager->setRepository($provider);
            $this->annotationManager->setSource($annotationSource);
        }
        return $this->annotationManager;
    }

	/**
	 * Вызов экшена контроллера
     *
	 * @param string $controllerName Название контроллера.
	 * @param string $actionName Метод.
	 * @param array|Data_Transport $input Входные данные.
	 * @param Controller_Task $task [optional] Задание
     * @param boolean $notLogging [optional] не логировать ли контроллер
	 * @return Controller_Task
	 */
	public function call($controllerName, $actionName, $input = array(),
		$task = null, $notLogging = false)
	{
		if (Tracer::$enabled && !$notLogging) {
			Tracer::resetDeltaModelCount();
			Tracer::resetDeltaQueryCount();
			Tracer::begin(
                __CLASS__, __METHOD__, __LINE__, $controllerName, $actionName
            );
		}
        // Создает новое пустое с переданным контроллером/экшином если
        // не передано задание, которое необходимо подхватить. Если задание
        // передано, то будет использоваться его входной транспорт
		if (!$task) {
			$task = $this->createEmptyTask($controllerName, $actionName);
		} elseif (!$input) {
            $input = $task->getInput();
        }
        // Полуваем контроллер и запоминаем его транспорты и задание, чтобы
        // можно было их вернуть по завершению работы менеджера. Сделано для
        // того, чтобы корректно отрабатывали конструкции подмены экшина и
        // прочие
		$controller = $this->get($controllerName);
		$lastInput = $controller->getInput();
		$lastOutput = $controller->getOutput();
		$lastTask = $controller->getTask();
        // Если входной транспорт не передан или не установлен у подхваченного
        // задания, то используем транспорт менеджера контроллеров. Если
        // входные данные переданы в виде массива, то создает транспорт с
        // провайдером Buffer на основании этого массива
		if (is_null($input)) {
			$input = $this->getInput();
		} elseif (is_array($input)) {
            $input = $this->createTransport($input);
		}
        $output = $this->getOutput($task);
        // Подменяем транспорты, на полученные из менеджера/задания
        $controller
            ->setInput($input)
            ->setOutput($output)
            ->setTask($task);
		$config = $this->config();
        // Создает контекст вызова контроллера, отдаем его before-делегатам
        // менеджера контроллеров.
        $context = $this->createControllerContext($controller, $actionName);
        $delegees = $config->delegees;
        if ($delegees) {
            foreach ($delegees as $delegeeName) {
                $this->delegee($delegeeName)->call($controller, $context);
            }
        }
        $callable = array($controller, $actionName);
        // Начинаем транзацию для экшина контроллера и выполняем его. Транспорты
        // и задачу после этого возвращаем на место
        $output->beginTransaction();
        //print_r($this->eventManager()->getMap());die;
        if (!$controller->getTask()->getIgnore()) {
            $this->getExecutor($task)->execute($callable, $context->getArgs());
            $task->setTransaction($output->endTransaction());
            $this->eventManager()->notify(
                $controller->getName() . '/' . $actionName,
                array('task'  => $task)
            );
        }//die;
		$controller
			->setInput($lastInput)
			->setOutput($lastOutput)
			->setTask($lastTask);
		if (Tracer::$enabled && !$notLogging) {
			$deltaModelCount = Tracer::getDeltaModelCount();
			$deltaQueryCount = Tracer::getDeltaQueryCount();
			Tracer::incControllerCount();
			Tracer::end($deltaModelCount, $deltaQueryCount,
				memory_get_usage(), 0);
		}
		return $task;
	}

    /**
     * Создает контекст для контроллера
     *
     * @param Controller_Abstract $controller
     * @param string $actionName
     * @return IcEngine\Controller\ControllerContext
     */
    protected function createControllerContext($controller, $actionName)
    {
        $context = new IcEngine\Controller\ControllerContext();
        $context->setController($controller);
        $context->setAction($actionName);
        $context->setControllerManager($this);
        $defaultContext = $this->getDefaultContext();
        $context->setArgs(array(
            'context'   => $defaultContext
        ));
        return $context;
    }

    /**
     * Создает новый контекст по умолчанию
     *
     * @return Objective
     */
    protected function createDefaultContext()
    {
         $config = $this->config();
         $defaultContext = $config->context;
         if ($defaultContext) {
            $services = array();
            foreach ($defaultContext->__toArray() as $argName => $serviceName) {
                $services[$argName] = $this->getService($serviceName);
            }
            return new Objective($services);
         }
    }

    /**
     * Создает опции вызова контроллера по умолчанию
     *
     * @param array $options
     * @return array
     */
    protected function createEmptyOptions($options)
    {
        $options = array('full_result' => !$options);
        return $options;
    }

    /**
     * Создает новое пустое задание
     *
     * @param string $controller
     * @param string $action
     * @return \Controller_Task
     */
    protected function createEmptyTask($controller, $action)
    {
        $task = new Controller_Task(array(
            'id'			=> null,
            'controller'	=> $controller,
            'action'		=> $action,
            'assign'        => '',
            'sort'          => 0
        ));
        return $task;
    }

    /**
     * Создает новый экшин из данных массива
     *
     * @param array $action
     * @return \Controller_Action
     */
    protected function createFromArray($action)
    {
        return new Controller_Action(array (
            'controller'	=> $action['controller'],
            'action'		=> $action['action']
        ));
    }

    /**
     * Создает результат работы контроллера по умолчанию
     *
     * @param array $buffer
     * @return array
     */
    protected function createResult($buffer)
    {
        return array (
			'error' => isset($buffer['error']) ? $buffer['error'] : '',
			'data'	=> isset($buffer['data']) ? $buffer['data'] : array(),
			'html'	=> null
		);
    }

	/**
	 * Создаем задания из экшинов
     *
	 * @param array $actions
	 * @param Data_Transport $input
	 * @return array
	 */
	public function createTasks($actions, Data_Transport $input)
	{
		$tasks = array();
		foreach ($actions as $action) {
			$task = new Controller_Task($action);
			$task->setInput($input);
			$tasks[] = $task;
		}
		return $tasks;
	}

    /**
     * Создает новый транспорт и начинает транзакцию для него
     *
     * @param array $input
     * @return \Data_Transport
     */
    protected function createTransport($input)
    {
        $transport = new Data_Transport();
        $transport->beginTransaction()->send($input);
        return $transport;
    }

    /**
     * Получить делегата менеджера контроллеров по имени
     *
     * @param string $delegeeName
     * @return IcEngine\Controller\ControllerManagerDelegeeAbstract
     */
    public function delegee($delegeeName)
    {
        if (!isset($this->delegees[$delegeeName])) {
            $delegee = new $delegeeName;
            $this->delegees[$delegeeName] = $delegee;
        }
        return $this->delegees[$delegeeName];
    }

    /**
     * Получить менеджер событий
     *
     * @return Event_Manager
     */
    public function eventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = new Event_Manager;
        }
        return $this->eventManager;
    }

	/**
	 * Очистка результатов работы контроллеров
	 */
	public function flushResults()
	{
		$this->tasksResults = array();
	}

	/**
	 * Возвращает контроллер по названию
     *
	 * @param string $controller_name
	 * @return Controller_Abstract
	 */
	public function get($controllerName)
	{
        $className = 'Controller_' . $controllerName;
        if (!class_exists($className)) {
            throw new Exception("Controller $controllerName not found.");
        }
        $controller = new $className;
        return $controller;
	}

    /**
     * Вернуть менеджер аннотаций
     *
     * @return Annotation_Manager_Abstract
     */
    public function getAnnotationManager()
    {
        return $this->annotationManager;
    }

    /**
	 * Настройки кэширования для контроллера-экшена.
	 *
     * @param string $controller Контроллер
	 * @param string $action Экшен
	 * @return Objective
	 */
	protected function getCacheConfig($controller, $action)
	{
		$selfConfig = $this->config();
        $controllerAction = $controller . '::' . $action;
        $controllerConfig = $selfConfig->actions[$controllerAction];
        if (!$controllerConfig) {
            if (!$selfConfig->actions[$controller]) {
                return;
            }
            $controllerConfig = $selfConfig->actions[$controller];
        }
        if ($controllerConfig->cache_config) {
            return call_user_func(
                $controllerConfig->cache_config, $controllerConfig
            );
        }
        if ($controllerConfig->tags && $controllerConfig->tag_provider) {
            $provider = $this->getService('dataProviderManager')->get(
                $controllerConfig->tag_provider
            );
            $tagNames = $controllerConfig->tags->__toArray();
            $tags = $provider->getTags($tagNames);
            $controllerConfig->current_tags = $tags;
        }
        return $controllerConfig;
	}

    /**
     * Получить контекст контроллера по умолчанию
     *
     * @return Objective
     */
    public function getDefaultContext()
    {
        if (is_null($this->defaultContext)) {
            $this->defaultContext = $this->createDefaultContext();
        }
        return $this->defaultContext;
    }

    /**
     * Получить "выполнитель" по умолчанию
     *
     * @return Controller_Manager_Executor_Abstract
     */
    public function getDefaultExecutor()
    {
        if (!isset($this->defaultExecutor)) {
            $this->defaultExecutor = new Controller_Manager_Executor_Simple();
        }
        return $this->defaultExecutor;
    }

    /**
     * Получить выходной транспорт по умолчанию
     *
     * @return Data_Transport
     */
    public function getDefaultOutput()
    {
        if (is_null($this->defaultOutput)) {
            $this->defaultOutput = new Data_Transport();
        }
        return $this->defaultOutput;
    }

    /**
     * Получить менеджер событий
     *
     * @return Event_Manager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Получить "выполнитель" для задания
     *
     * @param Controller_Task $task
     * @param Controller_Manager_Executor_Abstract $executor
     */
    public function getExecutor($task)
    {
        $key = $this->taskKey($task);
        if (isset($this->executors[$key])) {
            return $this->executors[$key];
        }
        return $this->getDefaultExecutor();
    }

	/**
     * Получить входной транспорт по умолчанию
     *
	 * @return Data_Transport
	 */
	public function getInput()
	{
		if (!$this->input) {
			$this->input  = new Data_Transport();
		}
		return $this->input;
	}

	/**
	 * Возвращает транспорт для выходных данных по умолчанию.
	 *
     * @param Controller_Task $task
     * @return Data_Transport
	 */
	public function getOutput($task)
	{
        $key = $this->taskKey($task);
        if (!isset($this->outputs[$key])) {
            return $this->getDefaultOutput();
        }
        return $this->outputs[$key];
	}

    /**
     * Получить инжектор сервисов
     *
     * @return Service_Injector_Abstract
     */
    public function getServiceInjector()
    {
        return $this->serviceInjector;
    }

	/**
	 * Выполняет указанный контроллер, экшен с заданными параметрами
     *
	 * @param string $controllerAction Название контроллера или контроллер и
     * экшен в формате "Controller/action".
	 * @param array $args Параметры контроллера.
	 * @param mixed $options=true Параметры вызова.
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public function html($controllerAction, $args = array(),
		$options = true)
	{
        $controllerAction = explode('/', $controllerAction);
		if (!isset($controllerAction[1])) {
            $controllerAction[1] = self::DEFAULT_ACTION;
        }
        $cacheConfig = $this->getCacheConfig(
            $controllerAction[0], $controllerAction[1]
        );
        if (is_bool($options)) {
            $options = $this->createEmptyOptions($options);
        }
		$html = $this->getService('executor')->execute(
			array($this, 'htmlUncached'),
			array($controllerAction, $args, $options),
			$cacheConfig
		);
		return $html;
	}

	/**
	 * Выполняет указанный контроллер, экшен с заданными параметрами,
	 * не используется кэширование
     *
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
	public function htmlUncached($controllerAction, $args = array(),
        $options = true)
	{
		$controllerAction = is_array($controllerAction)
            ? $controllerAction : explode('/', $controllerAction);
        if (!isset($controllerAction[1])) {
            $controllerAction[1] = self::DEFAULT_ACTION;
        }
		if (Tracer::$enabled) {
			Tracer::resetDeltaModelCount();
			Tracer::resetDeltaQueryCount();
			Tracer::begin(
                __CLASS__, __METHOD__, __LINE__,
                $controllerAction[0], $controllerAction[1]
            );
		}
        $task = $this->call(
            $controllerAction[0], $controllerAction[1], $args, null, true
        );
        $this->lastError = null;
		$buffer = $task->getTransaction()->buffer();
		$result = $this->createResult($buffer);
        $template = $task->getTemplate();
        if (Tracer::$enabled) {
            $startTime = microtime(true);
        }
        if ($template) {
            $result['html'] = $this->renderTemplate($buffer, $template);
        }
        if ($this->lastError) {
            $result['error'] = $this->lastError;
        }
		if (Tracer::$enabled) {
            $endTime = microtime(true);
			$deltaModelCount = Tracer::getDeltaModelCount();
			$deltaQueryCount = Tracer::getDeltaQueryCount();
			$delta = $endTime - $startTime;
			Tracer::incRenderTime($delta);
			Tracer::incControllerCount();
			Tracer::end($deltaModelCount, $deltaQueryCount, memory_get_usage(),
				$delta);
		}
		if (!empty($options['with_buffer'])) {
			$options = array('full_result' => true);
			$result['buffer'] = $buffer;
		} elseif (is_bool($options)) {
            $options = $this->createEmptyOptions($options);
        }
		return !empty($options['full_result']) ? $result : $result['html'];
	}

    /**
     * Логирует ошибку
     *
     * @param Exception $e
     */
    public function logError($e)
    {
         $msg = '[' . $e->getFile() . '@' .
            $e->getLine() . ':' .
            $e->getCode() . '] ' .
            $e->getMessage() . PHP_EOL;
        error_log(
            $msg . PHP_EOL .
            $e->getTraceAsString() . PHP_EOL,
            E_USER_ERROR, 3
        );
        $this->getService('debug')->log($msg);
    }

	/**
	 * Добавление задания в текущую очередь выполнения
     *
	 * @param mixed $action
	 */
	public function pushTasks($action)
	{
        $actions = array();
        if (!is_array($action)) {
            $actions = array($action);
        } else {
            if (!isset($action[0])) {
                $action = $this->createFromArray($action);
            }
            $actions = $action;
        }
        foreach ($actions as $action) {
            if (!($action instanceof Controller_Action)) {
                throw new Exception('Illegal action type');
            }
            self::$taskQueue[] = new Controller_Task($action);
        }
	}

    /**
     * Рендерит шаблон
     *
     * @param array $buffer
     * @param string $template
     */
    public function renderTemplate($buffer, $template)
    {
        $viewRenderManager = $this->getService('viewRenderManager');
        $view = $viewRenderManager->pushViewByName(self::DEFAULT_VIEW);
        try {
            $view->assign($buffer);
            $html = $view->fetch($template);
        } catch (Exception $e) {
            $this->lastError = 'Controller_Manager: Error in template.';
            $this->logError($e);
        }
        $viewRenderManager->popView();
        return !empty($html) ? $html : null;
    }

	/**
	 * Запустить задание на выполнение
     *
	 * @param Controller_Task $task
	 * @return Controller_Task
	 */
	public function run($task)
	{
		$parentTask = $this->currentTask;
		$this->currentTask = $task;
		$action = $task->controllerAction();
		$task = $this->call(
			$action['controller'],
			$action['action'],
			$task->getInput(),
			$task
		);
		$this->currentTask = $parentTask;
		return $task;
	}

	/**
	 * Выполнение очереди заданий
     *
	 * @param array $tasks
	 * @return array
	 */
	public function runTasks($tasks)
	{
		$this->tasksBuffer[] = $this->tasksQueue;
		$this->tasksResultsBuffer[] = $this->tasksResults;
		$this->tasksQueue = $tasks;
		$this->tasksResults = array();
        $taskCount = count($this->tasksQueue);
		for ($i = 0; $i < $taskCount; ++$i) {
			$task = $this->run($this->tasksQueue[$i]);
			if (!$task->getIgnore()) {
				$this->tasksResults[] = $task;
			}
		}
		$result = $this->tasksResults;
		$this->tasksQueue = array_pop($this->tasksBuffer);
		$this->tasksResults = array_pop($this->tasksResultsBuffer);
		return $result;
	}

    /**
     * Получить инжектор сервисов
     *
     * @return Service_Injector_Abstract
     */
    public function serviceInjector()
    {
        if (!$this->serviceInjector) {
            $injector = $this->getService('serviceInjector');
            $this->serviceInjector = $injector->get(
                'Context', $this->serviceLocator
            );
        }
        return $this->serviceInjector;
    }

    /**
     * Изменить менеджер аннотаций
     *
     * @param Annotation_Manager_Abstract $annotationManager
     */
    public function setAnnotationManager($annotationManager)
    {
        $this->annotationManager = $annotationManager;
    }

    /**
     * Изменить контекст контроллера по умолчанию
     *
     * @param Objective $defaultContext
     */
    public function setDefaultContext($defaultContext)
    {
        $this->defaultContext = $defaultContext;
    }

    /**
     * Изменить "выполнитель" по умолчанию
     *
     * @param Controller_Manager_Executor_Abstract $defaultExecutor
     */
    public function setDefaultExecutor($defaultExecutor)
    {
        $this->defaultExecutor = $defaultExecutor;
    }

    /**
     * Изменить менеджер событий
     *
     * @param Event_Manager $eventManager
     */
    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Изменить "выполнитель" для действия контроллера
     *
     * @param Controller_Task $task
     * @param Controller_Manager_Executor_Abstract $executor
     */
    public function setExecutor($task, $executor)
    {
        $key = $this->taskKey($task);
        $this->executors[$key] = $executor;
    }

    /**
     * Изменить выходной транспорт по умолчанию
     *
     * @param Data_Transport $defaultOutput
     */
    public function setDefaultOutput($defaultOutput)
    {
        $this->defaultOutput = $defaultOutput;
    }

    /**
     * Изменить выходной транспорт по умолчанию
     *
     * @param Data_Transport $output
     */
    public function setOutput($task, $output)
    {
        $key = $this->taskKey($task);
        $this->outputs[$key] = $output;
    }

    /**
     * Изменить инжектор сервисов
     *
     * @param Service_Injector_Abstract $serviceInjector
     */
    public function setServiceInjector($serviceInjector)
    {
        $this->serviceInjector = $serviceInjector;
    }

    /**
     * Получить ключ задания
     *
     * @param Controller_Task $task
     * @return string
     */
    public function taskKey($task)
    {
        return implode('/', $task->controllerAction());
    }
}