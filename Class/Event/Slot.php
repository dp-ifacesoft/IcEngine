<?php

/**
 * Слоты событий
 *
 * @author morph
 */
class Event_Slot
{
	/**
	 * Делигат слота
	 *
	 * @var string
	 */
	protected $delegee;

    /**
     * Менеджер событий
     * 
     * @var Event_Manager
     */
    protected $manager;
    
	/**
	 * Название
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Параметры слота
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Конструктор
	 *
	 * @param string $name
	 */
	public function __construct($name = null)
	{
		$this->name = $name;
        $serviceLocator = IcEngine::serviceLocator();
        $this->manager = $serviceLocator->getService('eventManager');
	}

	/**
	 * Действие слота
	 */
	public function action()
	{
		if (!is_null($this->delegee)) {
			list($className, $methodName) = explode('::', $this->delegee);
			$classReflection = new ReflectionClass($className);
			$methodReflection = $classReflection->getMethod($methodName);
			$params = $methodReflection->getParameters();
			$resultParams = array();
			foreach ($params as $param) {
				$paramName = $param->getName();
				$resultParams[] = isset($this->params[$paramName])
					? $this->params[$paramName] : null;
			}
			call_user_func_array($this->delegee, $resultParams);
		}
	}

    /**
     * Получить менеджера событий
     * 
     * @return Event_Manager
     */
    public function getManager()
    {
        return $this->manager;
    }
    
	/**
	 * Получить имя слота
	 *
	 * @return string
	 */
	public function getName()
	{
		if ($this->name) {
			return $this->name;
		}
		return substr(get_class($this), strlen(__CLASS__) + 1);
	}

	/**
	 * Получить параметры слота
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Получить сигнал по имени
	 *
	 * @param string $signalName
	 * @return Event_Signal
	 */
	protected function getSignal($signalName)
	{
		return $this->manager->getSignal($signalName);
	}

	/**
	 * Зарегистрировать слот на сиглал
	 *
	 * @param Event_Signal $signal
	 */
	public function register($signal)
	{
		if (is_string($signal)) {
			$signal = $this->getSignal($signal);
		}
		$this->manager->register($signal, $this);
	}

	/**
	 * Изменить дегелат слота
	 *
	 * @param string $delegee
	 */
	public function setDelegee($delegee)
	{
		$this->delegee = $delegee;
	}

	/**
	 * Изменить параметры слота
	 *
	 * @param array $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Снять регистрацию слота со сигнала
	 *
	 * @param Event_Signal $signal
	 */
	public function unregister($signal)
	{
		if (is_string($signal)) {
			$signal = $this->getSignal($signal);
		}
		$this->manager->unregister($signal, $this);
	}
}