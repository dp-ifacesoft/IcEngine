<?php

/**
 * Абстрактный класс менеджера
 *
 * @author morph, goorus
 */
abstract class Manager_Abstract
{
    /**
     * Менеджер аннотаций
     *
     * @var Annotation_Manager_Abstract
     */
    protected $annotationManager;
    
	/**
     * Конфигурация
     *
	 * @var array
	 */
	protected $config = array();
    
    /**
     * Менеджер провайдеров
     *
     * @var Data_Provider_Manager
     */
    protected $dataProviderManager;
    
    /**
     * Менеджер событий
     *
     * @var Event_Manager
     */
    protected $eventManager;

    /**
     * Локатор сервисов
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
            $this->annotationManager = new Annotation_Manager_Standart();
            $provider = $this->dataProviderManager()->get('Annotation');
            $annotationSource = new Annotation_Source_Standart();
            $this->annotationManager->setRepository($provider);
            $this->annotationManager->setSource($annotationSource);
        }
        return $this->annotationManager;
    }
    
	/**
	 * Получить конфигурацию
	 *
     * @return Objective
	 */
	public function config()
	{
		if (is_array($this->config)) {
            $configManager = $this->getService('configManager');
			$config = $configManager->get(get_class($this), $this->config);
            if ($config) {
                $this->config = $config;
            } else {
                $this->config = new Objective(array());
            }
		}
		return $this->config;
	}
    
    /**
     * Получить менеджер провайдеров данных
     * 
     * @return Data_Provider_Manager
     */
    public function dataProviderManager()
    {
        if (!$this->dataProviderManager) {
            $this->dataProviderManager = $this->getService(
                'dataProviderManager'
            );
        }
        return $this->dataProviderManager;
    }
    
    /**
     * Получить менеджер событий
     *
     * @return Event_Manager
     */
    public function eventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = $this->getService('eventManager');
        }
        return $this->eventManager;
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
     * Получить менеджер провайдеров данных
     * 
     * @return Data_Provider_Manager
     */
    public function getDataProviderManager()
    {
        return $this->dataProviderManager;
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
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (!$this->serviceLocator) {
            $this->serviceLocator = IcEngine::serviceLocator();
        }
        return $this->serviceLocator->getService($serviceName);
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
     * Изменить менеджер провайдеров данных
     * 
     * @param Data_Provider_Manager $dataProviderManager
     */
    public function setDataProviderManager($dataProviderManager)
    {
        $this->dataProviderManager = $dataProviderManager;
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
     * Получить текущий локатор сервисов
     *
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Изменить текущий локатор сервисов
     *
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}