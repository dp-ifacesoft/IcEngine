<?php

/**
 * Базовый класс контроллера
 *
 * @author goorus, morph
 */
class Controller_Abstract
{
	/**
	 * Последний вызванный экшен. В случае, если был вызван replaceAction,
     * может отличаться от $task
     *
	 * @var string
	 */
	protected $currentAction;

    /**
     * Локатор сервисов
     *
     * @var Service_Locator
     */
    protected static $serviceLocator;

	/**
	 * Текущая задача
     *
	 * @var Controller_Task
	 */
	protected $task;

	/**
	 * Входные данные
     *
	 * @var Data_Transport
	 */
	protected $input;

	/**
	 * Выходные данные
     *
	 * @var Data_Transport
	 */
	protected $output;

	/**
	 * Конфиг контроллера
     *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Завершение работы контроллера ошибкой
     *
	 * @param string $text Текст ошибки. Не отображается пользователю,
	 * виден в консоли отладки.
	 * @param string $method Экшен, в котором произошла ошибка (__METHOD__)
	 * или шаблон (в этому случае метод будет взять из _currentAction).
	 * Необходим для определения шаблона. Если не передан, будет
	 * взято из $text.
	 * @param string $tpl [optional] Шаблон.
	 */
	protected function sendError($text, $method = null, $tpl = false)
	{
		$this->output->send(array(
			'error'	=> array(
				'text'	=> $text,
				'tpl'	=> $tpl
			),
		));
		if (!is_bool($tpl)) {
			$this->task->setClassTpl($method, $tpl);
		} elseif ($method) {
			if (strpos($method, '/') === false) {
				$this->task->setClassTpl(
                    $this->currentAction, '/' . ltrim($method, '/')
				);
			} else {
				$this->task->setClassTpl($method);
			}
		}
	}

	/**
	 * Загружает и возвращает конфиг для контроллера
     *
	 * @return Objective
	 */
	public function config()
	{
		if (is_array($this->config)) {
			$configManager = $this->getService('configManager');
            $this->config = $configManager->get(
                get_class($this), $this->config
			);
		}
		return $this->config;
	}

    /**
     * Получить аннотацию текущего контроллера
     *
     * @return Annotation_Set
     */
    public function getAnnotations()
    {
        return $this->getService('controllerManager')->annotationManager()
            ->getAnnotation($this);
    }

    /**
     * Получить текущий входной транспорт
     *
	 * @return Data_Transport
	 */
	public function getInput()
	{
		return $this->input;
	}

    /**
     * Получить название контроллера без префикса Controller_
     *
     * @return string
     */
    public function getName()
    {
        return substr(get_class($this), strlen('Controller_'));
    }

    /**
     * Получить текущий выходной транспорт
     *
	 * @return Data_Transport
	 */
	public function getOutput()
	{
		return $this->output;
	}

    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (!self::$serviceLocator) {
            self::$serviceLocator = IcEngine::serviceLocator();
        }
        return self::$serviceLocator->getService($serviceName);
    }

    /**
     * Получить сервис локатор
     *
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return self::$serviceLocator;
    }

	/**
	 * Возвращает текущую задачу контролера
     *
	 * @return Controller_Task
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Имя контроллера (без приставки Controller_)
     *
	 * @return string
	 */
	final public function name()
	{
		return substr(get_class($this), strlen('Controller_'));
	}

	/**
	 * Заменить текущий экшн с передачей всех параметров
     *
     * @param string $controller
     * @param string $action
	 */
	public function replaceAction($controller, $action = 'index')
	{
		if ($controller instanceof Controller_Abstract) {
			$other = $controller;
			$controller = $other->name();
		} else {
            $controllerManager = $this->getService('controllerManager');
			$other = $controllerManager->get($controller);
		}
        $controllerAction = implode(
            '/', $this->task->controllerAction()
        );
        $this->input->send(array(
            'origin'    => $controllerAction
        ));
        $eventManager = $this->getService('eventManager');
        $signal = $eventManager->getSignal($controllerAction);
        $slot = $eventManager->getSlot('Controller_After');
        $signal->unbind($slot);
		if ($controller != get_class($this)) {
            $controller = $other;
			$controller->setInput($this->input);
			$controller->setOutput($this->output);
            if (!$controller->getTask()) {
                $controller->setTask($this->task);
            }
		} else {
            $controller = $this;
        }
        $controller->getTask()->setControllerAction(array(
            'controller'    => $controller->name(),
            'action'        => $action
        ));
        $reflection = new \ReflectionMethod($controller, $action);
        $params = $reflection->getParameters();
        $currentInput = $controller->getInput();
        $provider = $currentInput->getProvider(0);
        $resultParams = array();
        if ($params) {
            foreach ($params as $param) {
                $value = $currentInput->receive($param->name);
                if (!$value && $param->isOptional()) {
                    $value = $param->getDefaultValue();
                }
                if ($provider) {
                    $provider->set($param->name, $value);
                }
                $resultParams[$param->name] = $value;
            }
        }
        $reflection->invokeArgs($controller, $resultParams);
        $controller->task->setTemplate(
			'Controller/' . str_replace('_', '/', $controller->name()) . 
                '/' . $action
		);
	}

	/**
	 * Заменить текущую задачу контроллера
     *
	 * @param Controller_Task $task
	 * @return Controller_Abstract
	 */
	public function setTask($task)
	{
		$this->task = $task;
		return $this;
	}

	/**
	 * Устанавливает транспорт входных данных
     *
	 * @param Data_Transport $input
	 * @return Controller_Abstract
	 */
	public function setInput($input)
	{
		$this->input = $input;
		return $this;
	}

	/**
	 * Устанавливает транспорт выходных данных
     *
	 * @param Data_Transport $output
	 * @return Controller_Abstract
	 */
	public function setOutput($output)
	{
		$this->output = $output;
		return $this;
	}

    /**
     * Изменить локатор сервисов
     *
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }
}