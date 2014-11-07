<?php

/**
 * Абстрактный класс загрузчика
 *
 * @author goorus, morph
 */
abstract class Bootstrap_Abstract
{
	/**
	 * Путь до начала структуры Ice.
	 *
     * @var string
	 */
	protected $basePath;

	/**
	 * Название бутстрапа
	 *
     * @var string
	 */
	protected $name;

	/**
	 * Флаг выполненного бутстрапа.
	 *
     * @var boolean
	 */
	protected $runned = false;

    /**
     * Добавленные стратегии фронт контроллера
     * 
     * @var array
     */
    protected static $strategies = array();
    
	/**
	 * Возвращает загрузчик.
	 *
     * @param string $path путь до этого загрузчика
	 */
	public function __construct($path = null)
	{
		$this->basePath = substr($path, 0,
			- strlen('Model_' . get_class($this) . '.php')
		);
		$this->name = substr(get_class($this), strlen('Bootstrap_'));
	}

	/**
	 * Добавление путей в лоадер
	 */
	public function addLoaderPathes()
	{
		$path = $this->basePath();
        $loader = IcEngine::getLoader();
		$loader->addPath('Class', $path . 'Class/');
		$loader->addPath('Class', $path . 'Model/');
		$loader->addPath('Class', $path);
		$loader->addPath('Controller', $path . 'Controller/');
		$loader->addPath('Vendor', $path . 'Vendor/');
	}

	/**
	 * Возвращает путь до начала структуры Ice.
	 *
     * @return string.
	 */
	public function basePath()
	{
		return $this->basePath;
	}

    /**
	 * Запускает загрузчик.
	 */
	protected function bootstrapRun()
	{
		$this->addLoaderPathes();
		$this->initDds();
		$this->initAttributeManager ();
		$this->initModelScheme($this->name());
		$this->initModelManager();
		$this->initView();
		$this->initUser();
	}
    
    /**
     * Получить сервис по имени
     * 
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
    
    /**
     * Получить добавленные стратегии
     * 
     * @return array
     */
    public function getStrategies()
    {
        return static::$strategies;
    }

	/**
	 * Инициализация менеджера атрибутов.
	 */
	public function initAttributeManager()
	{

	}

	/**
	 * Инициализация источника данных по умолчанию.
	 */
	public function initDds($sourceName = 'default')
	{
        $serviceLocator = IcEngine::serviceLocator();
        $dataSourceManager = $serviceLocator->getService('dataSourceManager');
        $dds = $serviceLocator->getService('dds');
        $dataSource = $dataSourceManager->get($sourceName);
        $dds->setDataSource($dataSource);
	}

	/**
	 * Инициализация менеджера моделей и менеджера коллекций.
	 */
	public function initModelManager()
	{

	}

	/**
	 * Инициализация схемы моделей.
	 *
     * @param string $config
	 */
	public function initModelScheme($name)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
        $configManager = $serviceLocator->getService('configManager');
        $config = $configManager->get('Model_Scheme', $name);
        $modelScheme->init($config);
        $modelScheme->setBehavior($name);
	}

	/**
	 * Инициализация пользователя и сессии.
	 */
	public function initUser()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $serviceLocator->getService('userGuest')->init();
        $serviceLocator->getService('user')->init();
	}

	/**
	 * Инициализация рендера.
	 */
	public function initView()
	{
        $serviceLocator = IcEngine::serviceLocator();
		$serviceLocator->getService('viewRenderManager')->getView();
	}

	/**
	 * Возвращает название загрузчика.
	 *
     * @return string
	 */
	public function name()
	{
		return $this->name;
	}

	/**
	 * Запускает загрузчик, если этого не было сделано ранее.
	 */
	public function run()
	{
		if (!$this->runned) {
			$this->runned = true;
			$this->bootstrapRun();
		}
	}
}