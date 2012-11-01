<?php

/**
 * Менеджер модулей
 *
 * @author morph, neon
 */
class Module_Manager extends Manager_Abstract
{
    /**
     * @inheritdoc
     */
    protected static $_config = array(
        'configPath'        => 'Config',
        'defaultModule'     => 'Ice',
        'fromModel'         => false,
        'loaderPaths'       => array(
            'Class'         => 'Class',
            'Model'         => 'Class',
            ''              => 'Class',
            'Controller'    => 'Controller',
            'includes'      => 'includes'
        ),
        'modules'           => array(),
        'viewPath'          => 'View'
    );

	/**
	 * Текущие инициализированные модули
	 *
	 * @var array
	 */
	public static $modules = array();

	/**
	 * Добавить модуль в проект
	 *
     * @param string $moduleName Название модуля
	 */
	public static function addModule($moduleName)
	{
		if (isset(self::$modules[$moduleName])) {
			return;
		}
        $selfConfig = self::config();
        $moduleConfig = self::getConfig($moduleName);
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
            foreach ($pathTypes as $type => $paths) {
                foreach ($paths as $path) {
                    Loader::addPath($type, rtrim($path, '/') . '/');
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
            $configPath = IcEngine::root() . ltrim($configPath, '/');
        }
        if ($configPath) {
            Config_Manager::addPath(rtrim($configPath, '/') . '/');
        }
        if ($moduleName != $selfConfig->defaultModule) {
            Config_Manager::addPath(
                IcEngine::root() . $selfConfig->defaultModule .
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
            $view = View_Render_Manager::getView();
            $view->addTemplatesPath(rtrim($viewPath, '/') . '/');
        }
		self::$modules[$moduleName] = true;
	}

    /**
     * Получить конфиг для модуля
     *
     * @author morph
     */
    public static function getConfig($moduleName)
    {
        $resourceKey = self::resourceKey($moduleName);
        $tryConfig = Resource_Manager::get('Config', $resourceKey);
        if ($tryConfig) {
            return $tryConfig;
        }
        $baseConfigFile = IcEngine::root() . $moduleName . '/Config/Index.php';
        $selfConfig = self::config();
        $defaultModule = $selfConfig->defaultModule;
        $resultConfig = array();
        if (is_file($baseConfigFile)) {
            include_once($baseConfigFile);
            if (isset($config)) {
                $resultConfig = $config;
                unset($config);
            }
        }
        $overrideConfigFile = IcEngine::root() . $defaultModule .
            '/Config/Module/' . $moduleName . '.php';
        if (is_file($overrideConfigFile)) {
            include_onec($overrideConfigFile);
            if (isset($config)) {
                $resultConfig = array_merge($resultConfig, $config);
            }
        }
        $config = new Config_Array($resultConfig);
        if (!empty($resultConfig)) {
            Resource_Manager::set('Config', $resourceKey, $config);
        }
        return $config;
    }

    /**
     * Загрузка модулей
     */
	public static function init()
	{
        $config = self::config();
        if ($config->fromModel) {
            self::loadFromModel();
        } else {
            self::loadByNames((array) $config->modules);
        }
	}

    /**
     * Загрузить модули по именам
     *
     * @param array $names
     */
    public static function loadByNames($names)
    {
        foreach ($names as $name) {
			self::addModule($name);
		}
    }

    /**
     * Загрузить модули из модели
     */
    public static function loadFromModel()
    {
        $moduleCollection = Model_Collection_Manager::create('Module')
            ->addOptions(array(
               'name'   => '::Order_Desc',
               'field'  => 'id'
            ));
		foreach ($moduleCollection->raw() as $module) {
			self::addModule($module['name']);
		}
    }

    /**
     * Получить ключ ресурса
     *
     * @param string $moduleName
     * @return string
     */
    public static function resourceKey($moduleName)
    {
        return 'Module/' . $moduleName;
    }
}