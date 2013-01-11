<?php

namespace IcEngine\Controller;

/**
 * Менеджер контроллеров
 *
 * @author goorus, morph
 */
class ControllerManager extends IcEngine\Manager\ManagerAbstract
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
            'user'              => 'user'
        ),
        
        /**
         * Делигата менеджера контроллеров по умолчанию
         */
        'delegees'           => array(
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeParam',
            'IcEngine\\Controller\\Manager\\ControllerManagerDelegeeContext'
        )
	);

	/**
	 * Текущее задание
     *
	 * @var Controller_Task
	 */
	protected $currentTask;

    /**
     * Созданные делегита менеджера контроллеров
     * 
     * @var array
     */
    protected $deleeges = array();
    
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
	 * ранспорт выходных данных
     *
	 * @var Data_Transport
	 */
	protected $output;

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
     * Локатор услуг
     *
     * @var Service_Locator
     */
    protected $serviceLocator;

    /**
     * Получить менеджер аннотаций
     *
     * @return Annotation_Manager_Abstract
     */
    public function annotationManager()
    {
        if (!$this->annotationManager) {
            $this->annotationManager = new 
                IcEngine\Annotation\AnnotationManagerStandart();
            $dataProviderManager = $this->serviceLocator()->getService(
                'dataProviderManager'
            );
            $provider = $dataProviderManager->get('Annotation');
            $annotationSource = new IcEngine\Annotation\AnnotationSourceSimple();
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
		if (!$task) {
			$task = $this->createEmptyTask($controllerName, $actionName);
		}
		$controller = $this->get($controllerName);
		$lastInput = $controller->getInput();
		$lastOutput = $controller->getOutput();
		$lastTask = $controller->getTask();
		if (is_null($input)) {
			$input = $this->getInput();
		} elseif (is_array($input)) {
            $input = $this->createTransport($input);
		}
        $output = $this->getOutput();
        $controller
            ->setInput($input)
            ->setOutput($output)
            ->setTask($task);
		$output->beginTransaction();
        $config = $this->config();
        $defaultContext = $config->context;
        $context = new IcEngine\Controller\ControllerContext();
        $context->setController($controller);
        $context->setAction($actionName);
        $context->setControllerManager($this);
        if ($defaultContext) {
            $services = array();
            foreach ($defaultContext->__toArray() as $argName => $serviceName) {
                $services[$argName] = $this->serviceLocator()->getService(
                    $serviceName
                );
            }
            $params['context'] = new IcEngine\Core\Objective($services);
            $context->setArgs($params);
        }
        $delegees = $config->delegees;
        if ($delegees) {
            foreach ($delegees as $delegeeName) {
                $this->delegee($delegeeName)->call($controller, $context);
            }
        } 
        $callable = array($controller, $actionName);
        call_user_func_array($callable, $context->getArgs());
		$task->setTransaction($output->endTransaction());
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
        $task = new IcEngine\Controller\ControllerTask(array(
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
        return new IcEngine\Controller\ControllerAction(array (
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
			$task = new IcEngine\Controller\ControllerTask($action);
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
        $transport = new IcEngine\Data\Transport\DataTransport();
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
        $className = 'Controller' . $controllerName;
        if (!class_exists($className)) {
            throw new \Exception("Controller $controllerName not found.");
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
     * @return Data_Transport
	 */
	public function getOutput ()
	{
		if (!$this->output) {
			$this->output = new Data_Transport();
		}
		return $this->output;
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
     * Получить локатор сервисов
     *
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
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
	public function html($controllerAction, array $args = array(),
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
	public function htmlUncached($controllerAction, array $args=array (),
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
        Debug::log($msg);
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
            $injector = $this->serviceLocator()->getService(
                'serviceInjector'
            );
            $this->serviceInjector = $injector->get(
                'Context', $this->serviceLocator
            );
        }
        return $this->serviceInjector;
    }

    /**
     * Получить локатор сервисов
     *
     * @return Service_Locator
     */
    public function serviceLocator()
    {
        if (!$this->serviceLocator) {
            $this->serviceLocator = new Service_Locator;
        }
        return $this->serviceLocator;
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
     * Изменить инжектор сервисов
     *
     * @param Service_Injector_Abstract $serviceInjector
     */
    public function setServiceInjector($serviceInjector)
    {
        $this->serviceInjector = $serviceInjector;
    }

    /**
     * Изменить локатор сервисов
     *
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}