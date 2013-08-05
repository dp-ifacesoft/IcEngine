<?php

/**
 * Менеджер модулей
 *
 * @author morph, neon
 * @Service("moduleManager")
 */
class Module_Manager extends Manager_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'configPath'        => 'Config',
        'defaultModule'     => 'Ice',
        'fromModel'         => false,
        'loaderPaths'       => array(
            'Class'         => 'Class',
            'Model'         => 'Class',
            ''              => 'Class',
            'Controller'    => 'Controller',
            'Vendor'        => 'Vendor'
        ),
        'modules'           => array(),
        'viewPath'          => 'View'
    );
    
    /**
     * Конфигурация по умолчанию
     */
    protected $defaultConfig = array(
        'modules'   => array('Ice'),
        'fromModel' => false
    );

	/**
	 * Текущие инициализированные модули
	 *
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Добавить модуль в проект
	 *
     * @param string $moduleName Название модуля
	 */
	public function addModule($moduleName)
	{
		if (isset($this->modules[$moduleName])) {
			return;
		}
        $selfConfig = $this->config();
        $moduleConfig = $this->getConfig($moduleName);
        if ($moduleConfig->baseDir) {
            $moduleDir = $moduleConfig->baseDir;
            if ($moduleDir[0] != '/') {
                $moduleDir = IcEngine::root() . trim($moduleConfig->baseDir) .
                    '/';
            }
        } else {
            $moduleDir = IcEngine::root() . $moduleName . '/';
        }
        $loaderPaths = array();
        if ($selfConfig->loaderPaths) {
             $loaderPaths = $selfConfig->loaderPaths->__toArray();
        } else {
             $loaderPaths = array();
        }
        if ($moduleConfig->loaderPaths) {
            foreach ($moduleConfig->loaderPaths as $prefix => $value) {
                $loaderPaths[$prefix] = $value;
            }
        }
        if ($loaderPaths) {
            $pathTypes = array();
            foreach ($loaderPaths as $prefix => $value) {
                $pathTypes[$value][] = $prefix = !$prefix || $prefix[0] != '/'
                    ? $moduleDir . $prefix : $prefix;
            }
            $loader = $this->getService('loader');
            foreach ($pathTypes as $type => $paths) {
                foreach ($paths as $path) {
                    $loader->addPath($type, rtrim($path, '/') . '/');
                }
            }
        }
        $configPath = null;
        if (isset($moduleConfig->configPath)) {
            $configPath = $moduleConfig->configPath;
        } else {
            $configPath = $moduleName . '/' .
                ltrim($selfConfig->configPath, '/');
        }
        if (!empty($configPath) && $configPath[0] != '/') {
            $configPath = ltrim($configPath, '/');
        }
        $configManager = $this->getService('configManager');
        if ($configPath) {
            $configManager->addPath(rtrim($configPath, '/') . '/');
        }
        if ($moduleName != $selfConfig->defaultModule) {
            $configManager->addPath(
                $selfConfig->defaultModule .
                '/Config/Module/' . $moduleName . '/'
            );
        }
        $viewPath = null;
        if (isset($moduleConfig->viewPath)) {
            $viewPath = $moduleConfig->viewPath;
        } else {
            $viewPath = $moduleName . '/' . ltrim($selfConfig->viewPath, '/');
        }
        if (!empty($viewPath) && $viewPath[0] != '/') {
            $viewPath = IcEngine::root() . ltrim($viewPath, '/');
        }
        if ($viewPath) {
            $viewRenderManager = $this->getService('viewRenderManager');
            $view = $viewRenderManager->getView();
            $view->addTemplatesPath(rtrim($viewPath, '/') . '/');
        }
		$this->modules[$moduleName] = true;
        if ($moduleName != 'Ice') {
            $routeConfig = $this->getConfig($moduleName, 'Route');
            $routeService = $this->getService('route');
            if ($routeConfig->routes) {
                foreach ($routeConfig->routes->__toArray() as $route) {
                    $route['params']['module'] = $moduleName;
                    $routeService->addRoute($route);
                }
            }
        }
	}

    /**
     * Получить конфиг для модуля
     *
     * @param string $moduleName
     * @param string $name
     * @author morph
     */
    public function getConfig($moduleName, $className = 'Index')
    {
        $className = str_replace('_', '/', $className);
        $resourceKey = $this->resourceKey($moduleName, $className);
        $resourceManager = $this->getService('resourceManager');
        $tryConfig = $resourceManager->get('Config', $resourceKey);
        if ($tryConfig) {
            return $tryConfig;
        }
        $baseConfigFile = IcEngine::root() . $moduleName . '/Config/' .
            $className . '.php';
        $selfConfig = $this->config();
        $defaultModule = $selfConfig->defaultModule;
        $resultConfig = array();
        if (is_file($baseConfigFile)) {
            $resultConfig = include_once($baseConfigFile);
        }
        $overrideConfigFile = IcEngine::root() . $defaultModule .
            '/Config/Module/' . $moduleName . '.php';
        if (is_file($overrideConfigFile)) {
            $config = $overrideConfigFile;
            if (isset($config)) {
                $resultConfig = array_merge($resultConfig, $config);
            }
        }
        $config = new Config_Array($resultConfig);
        if (!empty($resultConfig)) {
            $resourceManager->set('Config', $resourceKey, $config);
        }
        return $config;
    }

    /**
     * Получить текущие модули
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Загрузка модулей
     */
	public function init()
	{
        if ($this->modules) {
            return;
        }
        $config = $this->config();
        if ($config->modules->count()) {
            $config = $config->__toArray();
        } else {
            $config = $this->defaultConfig;
        }
        if ($config['fromModel']) {
            $this->loadFromModel();
        } else {
            $this->loadByNames($config['modules']);
        }
	}

    /**
     * Загрузить модули по именам
     *
     * @param array $names
     */
    public function loadByNames($names)
    {
        foreach ($names as $name) {
			$this->addModule($name);
		}
    }

    /**
     * Загрузить модули из модели
     */
    public function loadFromModel()
    {
        $collectionManager = $this->getService('collectionManager');
        $moduleCollection = $collectionManager->create('Module')
            ->addOptions(
                array(
                    'name'   => '::Order_Desc',
                    'field'  => 'isMain'
                ),
                array(
                    'name'   => '::Order_Desc',
                    'field'  => 'id'
                )
            );
		foreach ($moduleCollection->raw() as $module) {
			$this->addModule($module['name']);
		}
    }

    /**
     * Получить ключ ресурса
     *
     * @param string $moduleName
     * @param string $configName
     * @return string
     */
    public function resourceKey($moduleName, $configName = 'Index')
    {
        return 'Module/' . $moduleName . '/' . $configName;
    }
}