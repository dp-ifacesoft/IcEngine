<?php
/**
 * Класс необходимый для инициализации фреймворка.
 *
 * @author goorus, morph 
 */
class IcEngine
{
    /**
	 * Загрузчик
	 *
     * @var Bootstrap_Abstract
	 */
    protected static $bootstrap;
    
    /**
     * Конфиг приложения
     * 
     * @var type 
     */
    protected static $config;
    
    /**
     * Конфиг приложения по умолчанию(в движке)
     * 
     * @var array 
     */
    protected static $defaultConfig;

    /**
     * Включен ли трейсер
     * 
     * @var array 
     */
    protected static $tracing = false;
    /**
	 * Экшин фронт контролера по умолчанию
	 *
     * @var string
	 */
    protected static $frontAction = 'index';

    /**
	 * Фронт контролер по умолчанию
	 *
     * @var string
	 */
    protected static $frontController = 'Front';

    /**
	 * Название транспорта по умолчанию
	 *
     * @var string
	 */
    protected static $frontInput = 'defaultInput';

    /**
	 * Рендер по умолчанию
	 *
     * @var string
	 */
    protected static $frontRender = 'Front';

    /**
     * Загрузчик
     *
     * @var Loader
     */
    protected static $loader;

    /**
	 * Зарегистрированные менеджеры
	 *
     * @var array
	 */
    protected static $managers = array();

    /**
	 * Путь до движка
	 *
     * @var string
	 */
    protected static $path;

    /**
	 * Путь до корня сайта.
	 *
     * @var string
	 */
    protected static $root;

    /**
	 * Задача фронт контроллера.
	 *
     * @var Controller_Task
	 */
    protected static $task;

    /**
     * Сервис локатор
     *
     * @var Service_Locator
     */
    protected static $serviceLocator;
    
    /**
     * Время запуска
     *
     * @var Service_Locator
     */
    protected static $startTime;

    /**
	 * Получить текущий бутстрап
	 *
     * @desc Bootstrap_Abstract
	 */
    public static function bootstrap()
    {
        return self::$bootstrap;
    }
    
    
    /**
     * Создать задание для front-контроллера
     * 
     * @return Controller_Front_Task
     */
    public static function createFrontControllerTask()
    {
        $action = self::createTaskAction();
        $task = new Controller_Front_Task($action);
        $viewRenderManager = self::getManager('View_Render');
        $viewRender = $viewRenderManager->byName(self::$frontRender);
        $task->setViewRender($viewRender);
        return $task;
    }

    /**
     * Создать экшин для фронт контроллера
     *
     * @return Controller_Action
     */
    protected static function createTaskAction()
    {
        $action = new Controller_Action(
            array(
                'id' => null,
                'controller' => self::$frontController,
                'action' => self::$frontAction
            )
        );
        return $action;
    }

    /**
     * Вывод результата работы.
     */
    public static function flush()
    {
        $controllerManager = self::getManager('Controller');
        $controllerManager->call(
            'Render', 'index', array('task' => self::$task)
        );
        if (self::$tracing) {
            self::renderTracer();
        }
    }

    /**
	 * Инициализация лоадера.
	 *
     * @param string $root Путь до корня сайта.
	 * @param string $bootstap Путь до загрузчика.
	 */
    public static function init($root = null, $bootstap = null)
    {

        // Запоминаем путь до движка
        self::$path = dirname(__FILE__) . '/';
        if (strlen(self::$path) < 2) {
            self::$path = '';
        }
        // путь до корня сайта
        self::$root = $root ? rtrim($root, '/\\') . '/' : self::getRoot();
        self::initLoader();
        self::$loader->load('Loader_Auto');
        $autoLoader = new Loader_Auto();
        $autoLoader->register();
        $loaderProvider = self::getManager('Data_Provider')->get('Loader');
        self::$loader->setProvider($loaderProvider);
        $configProvider = self::getManager('Data_Provider')->get('Config');
        self::getManager('Config')->setProvider($configProvider);
        if ($bootstap) {
            self::initBootstrap($bootstap);
        }
        self::serviceLocator()->registerService('loader', self::$loader);
        register_shutdown_function(array(__CLASS__, 'shutdownHandler'));
        
    }

    /**
     * Подключает загрузчик и запускает его.
     *
     * @param string $path Путь до загрузчика.
     */
    public static function initBootstrap($path)
    {
        require $path;
        $name = basename($path, '.php');
        require_once __DIR__ . '/Class/Bootstrap/Manager.php';
        $bootstrapManager = self::getManager('Bootstrap');
        self::$bootstrap = $bootstrapManager->get($name, $path);
    }

    /**
     * Подключение класса Debug
     */
    public static function initDebug()
    {
        require dirname(__FILE__) . '/Class/Debug.php';
        call_user_func_array(array('Debug', 'init'), func_get_args());
    }
    
    /**
     * Меняет опции в зависиомсти от агрументов
     * 
     */
    public static function debugStrategy()
    {
        if (isset ($_SERVER['argv'], $_SERVER['argc'])) {
            IcEngine::initDebug('dir:' . self::$config['log']);
        } else {
            ob_start();
            IcEngine::initDebug('dir:' . self::$config['log'], 'fb');
        }
    }

    /**
     * Инициализация лоадера.
     * 
     */
    public static function initLoader()
    {
        require dirname(__FILE__) . '/Class/Loader.php';
        self::$loader = new Loader();
        self::$loader->addPathes(
            array(
                'Class' => array(
                    self::$path . 'Service/',
                    self::$path . 'Class/',
                    self::$path . 'Model/',
                    self::$path
                ),
                'Controller' => array(
                    self::$path . 'Controller/'
                ),
                'Form' => array(
                    self::$path . 'View/'
                ),
                'Vendor' => self::$path . 'Vendor/'
            )
        );
    }

    /**
     * Подключение класса Tracer
     */
    public static function initTracer()
    {
        require dirname(__FILE__) . '/Class/Tracer.php';
    }

    /**
     * Получить frontTamplate
     *
     * @return string
     */
    public static function getFrontTemplate()
    {
        return self::$frontTemplate;
    }

    /**
     * Получить имя сервиса менеджера для локатора сервисов
     *
     * @param string $name
     * @return string
     */
    protected static function getNameForServiceLocator($name)
    {
        $parts = explode('_', $name);
        $parts[0] = strtolower($parts[0]);
        return implode('', $parts);
    }

    /**
     * Получить загрузчик по умолчанию
     *
     * @return Loader
     */
    public static function getLoader()
    {
        return self::$loader;
    }

    /**
     * Получить менеджера по имени
     *
     * @param string $name
     * @return Manager_Abstract
     */
    public static function getManager($name)
    {
        if (!isset(self::$managers[$name])) {
            $fromServiceLocator = false;
            $serviceName = self::getNameForServiceLocator($name . '_Manager');
            $manager = self::serviceLocator()->getService($serviceName);
            if ($manager) {
                $fromServiceLocator = true;
            }
            if (!$manager) {
                $className = $name . '_Manager';
                $manager = new $className;
            }
            self::registerManager($name, $manager, $fromServiceLocator);
        }
        return self::$managers[$name];
    }

    /**
     * Возвращает путь до корня сайта.
     *
     * @return string
     */
    protected static function getRoot()
    {
        return isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' 
            : rtrim(realpath(self::$path . '..'), '/') . '/';
    }

    /**
     * Получить сервис локатор
     *
     * @return Service_Locator
     */
    public static function getServiceLocator()
    {
        return self::$serviceLocator;
    }

    /**
     * Получить задание фронт контроллера
     * 
     * @return Controller_Front_Task
     */
    public static function getTask()
    {
        return self::$task;
    }

    /**
     * Путь до корня движка
     *
     * @return string
     */
    public static function path()
    {
        return self::$path;
    }

    /**
     * Зарегистрировать нового менеджера по имени
     *
     * @param string $name
     * @param Manager_Abstract $manager
     * @param boolean $fromServiceLocator
     */
    public static function registerManager($name, $manager, $fromServiceLocator = false)
    {
        self::$managers[$name] = $manager;
        if (!$fromServiceLocator) {
            $serviceName = self::getNameForServiceLocator($name);
            self::$serviceLocator->registerService($serviceName, $manager);
        }
    }

    /**
     * Путь до корня сайта.
     *
     * @return string
     */
    public static function root()
    {
        return self::$root;
    }

    /**
     * Запуск рабочего цикла и вывод результата.
     */
    public static function run()
    {
        self::setSession();
        self::$bootstrap->run();
        if (!self::$task) {
            self::$task = self::createFrontControllerTask();
        }
        $controllerManager = self::getManager('Controller');
        $transportManager = self::getManager('Data_Transport');
        $transport = $transportManager->get(self::$frontInput);
        self::$task->setStrategies(self::$bootstrap->getStrategies());
        try {
            $controllerManager->call(
                self::$frontController, self::$frontAction, $transport, self::$task
            );
        } catch (Exception $e) {
            die;
        }
    }
    
    /**
     * Установить сессию
     * 
     */
    public static function setSession()
    {
        $domain = explode('.', $_SERVER['HTTP_HOST']);
        array_splice($domain, 0, -2);
        $mainHost = implode('.', $domain);
        session_set_cookie_params(0, '/', '.' . $mainHost);
        self::serviceLocator()->getService('registry')->set('domain', $mainHost);
    }

    /**
     * Получить локатор сервисов
     *
     * @return Service_Locator
     */
    public static function serviceLocator()
    {
        if (!self::$serviceLocator) {
            self::$serviceLocator = new Service_Locator();
            $source = new Service_Source();
            self::$serviceLocator->setSource($source);
            $annotationManager = new Annotation_Manager_Standart();
            $annotationSource = new Annotation_Source_Standart();
            $annotationManager->setSource($annotationSource);
            $source->setLocator(self::$serviceLocator);
            $provider = new Data_Provider_Annotation();
            $provider->setPath('Ice/Var/Annotation/');
            $source->setAnnotationManager($annotationManager);
            $annotationManager->setRepository($provider);
        }
        return self::$serviceLocator;
    }

    /**
     * Изменить действие фронт контроллера
     *
     * @param string $action
     */
    public static function setFrontAction($action)
    {
        self::$frontAction = $action;
    }

    /**
     * Изменить название контроллера фронт контроллера
     *
     * @param string $controller
     */
    public static function setFrontController($controller)
    {
        self::$frontController = $controller;
    }

    /**
     * Изменить название транспорта для фронт контроллера
     *
     * @param string $inputName
     */
    public static function setFrontInput($inputName)
    {
        self::$frontInput = $inputName;
    }

    /**
     * Изменить название рендера для фронт контроллера
     *
     * @param string $renderName
     */
    public static function setFrontRender($renderName)
    {
        self::$frontRender = $renderName;
    }

    /**
     * Изменить загрузчик классов по умолчанию
     *
     * @param Loader $loader
     */
    public static function setLoader($loader)
    {
        self::$loader = $loader;
    }

    /**
     * Изменить путь до движка
     *
     * @param string $path
     */
    public static function setPath($path)
    {
        self::$path = $path;
    }

    /**
     * Изменить путь до корня
     *
     * @param string $root
     */
    public static function setRoot($root)
    {
        self::$root = $root;
    }

    /**
     * Изменить сервис локатор
     *
     * @param Service_Locator $serviceLocator
     */
    public static function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }

    /**
     * Обрабочик завершения приложения
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();
        $resourceManager = self::getManager('Resource');
        if (!$error) {
            $resourceManager->save();
            $shutdownManager = self::getManager('Shutdown');
            $shutdownManager->process();
        } else {
            $errno = $error['type'];
            if ($errno == E_ERROR || $errno == E_USER_ERROR) {
                if (!headers_sent()) {
                    header('HTTP/1.0 500 Internal Server Error');
                }
            }
        }
    }
    
    /**
     * Включить трейсер
     * 
     */
    public static function enableTracer($startTime)
    {
        self::$startTime = $startTime;
        self::$tracing = true;
    }
    
    /**
     * Отключить трейсер
     * 
     */
    public static function disableTracer()
    {
        self::$tracing = false;
    }
    
    /**
     * распечатать трейсер
     * 
     */
    public static function renderTracer()
    {
        $request = self::serviceLocator()->getService('request');
        if ($request->get('TRACER') || isset($_GET ['TRACER'])) {
                $endTime = microtime(true);
                Tracer::setTotalTime($endTime - self::$startTime);
                echo self::serviceLocator()->getService('controllerManager')
                ->html('Tracer/index');
        }
    }

    /**
     * Сеттер для конфига приложения
     * 
     * @param mixed $config
     */
    public static function setConfig($config)
    {
        self::$config = array_merge(self::getDefaultConfig(),$config);
    }
    
    /**
     * геттер для конфига приложения
     * 
     * @return mixed
     */
    public static function getConfig()
    {
        return self::$config;
    }
    
    /**
     * геттер для конфига приложения
     * 
     * @return mixed
     */
    public static function getDefaultConfig()
    {
        if (!isset(self::$defaultConfig) || empty(self::$defaultConfig)) {
            self::$defaultConfig = require realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Application.php');
        }
        return self::$defaultConfig;
    }
    
    
    /**
     * Установить сайт локейшн
     * 
     */
    public static function setSiteLocation($params)
    {
        $helperSiteLocation = IcEngine::serviceLocator()
                ->getService('helperSiteLocation');
        if (!empty($params['host'])) {
            $host = $params['host'];
        }
        if (empty($host)) {
                $host = $helperSiteLocation->get('host') ? 
                $helperSiteLocation->get('host') : 
                    $helperSiteLocation->getLocation();
        }
        if (empty($host)) {
                $host = self::$config['host'];
        }
        $_SERVER['HTTP_HOST'] = $host;
        $_SERVER['SERVER_NAME'] = $host;
        $registry = IcEngine::serviceLocator()->getService('registry');
        $registry->set('host', $host);
    }
    
    /**
     * Инициализация кли
     * 
     */
    public static function initCli($config)
    {
        self::setConfig($config);
        $argv = $config['argv'];
        $argc = $config['argc'];
        if (empty($argv) || $argc < 2) {
            echo 'Usage: ./ic "Controller/action"' . PHP_EOL;
            echo 'Default Controller/action is "'.  $config['cli']['defaultControllerAction'] . '"' . PHP_EOL;
            array_push($argv, $config['cli']['defaultControllerAction']);
        }
        if (!isset($argv) || !isset($argc)) {
            echo 'This script is for console use only.' . PHP_EOL;
            die;
        }
        $params = self::replaceArgs($argv);
        $_SERVER['DOCUMENT_ROOT'] = realpath('.');
        self::init(
            $config['application']['path'],
            $config['cli']['bootstrap']
        );
        self::setSiteLocation($params);
        self::setFrontController('Cli_Simple');
        self::setFrontRender('Cli');
        self::setFrontInput('cliInput');
        $userCliService = IcEngine::getServiceLocator()->getService('userCli');
        $userCliService->init();
        $sessionService = IcEngine::getServiceLocator()->getService('session');
        $sessionService->setDefaultUserId($userCliService->id());
    }
    
    /**
     * Заменить аргументы
     * 
     */
    public static function replaceArgs($argv)
    {
        $inputArgs = $argv;
               array_shift($argv);
        $controllerAction = array_shift($argv);
        $params = array();
        $param = null;
        foreach ($argv as $arg) {
            if (substr($arg, 0, 2) == '--') {
                $param = substr($arg, 2);
                if (!isset($params[$param])) {
                    $params[$param] = true;
                }
            } elseif ($param) {
                if (isset($params[$param]) && !is_bool($params[$param])) {
                    if (!is_array($params[$param])) {
                        $params[$param] = array($params[$param]);
                    }
                    $params[$param][] = $arg;
                } else {
                    $params[$param] = $arg;
                }
                $param = null;
            }
        }
        $argv = array_values($argv);
        $args = array(null, $controllerAction);
        foreach ($params as $param => $value) {
            foreach ((array) $value as $theValue) {
                $args[] = $param . '=' . $theValue;
            }
        }
        if (is_file(self::$config['author'])) {
            $author = file_get_contents(self::$config['author']);
            $args[] = 'author=' . $author;
        }
        if (end($inputArgs) == self::$config['cli']['help']) {
            $strpos = strpos($args[1], '/');
            if ($strpos !== false) {
                $controller = substr($args[1], 0, $strpos);
                $action = substr($args[1], $strpos + 1);
                $_SERVER['argv'] =  [
                    '1' =>  'Help',
                    '2' =>  strpos($controller, '_') ? $controller 
                        : 'name=Controller_' . $controller,
                    '3' =>  'method=' . $action
                ];
            } else {
                $_SERVER['argv'] = [
                    '1' =>  'Help',
                    '2' =>  strpos($args[1], '_') ? $args[1] 
                        : 'name=Controller_' . $args[1],
                ];
            }
        } else {
            $_SERVER['argv'] = $args;
        }
        return $params;
    }
    
    /**
     * Получить автора
     * 
     */
    public function getAuthor()
    {
        return file_exists(self::$config['author']) 
            ? file_get_contents(self::$config['author']) 
            : null;
    }
    
    /**
     * Получить папку контроллеров
     * 
     * @return string
     */
    public function getControllersDir()
    {
        return isset(self::$config['application']['controllersDir']) 
            ? self::$config['application']['controllersDir'] 
            : __DIR__ . '/../Ice/Controller/';
    }
    
    /**
     * Получить папку вьюшек
     * 
     * @return string
     */
    public function getViewsDir()
    {
        return isset(self::$config['application']['viewsDir']) 
            ? self::$config['application']['viewsDir'] 
            : __DIR__ . '/../Ice/View/';
    }
    
    /**
     * Получить папку js'ок
     * 
     * @return string
     */
    public function getJsDir($pack = 'noPack')
    {
        $dir = isset(self::$config['application']['jsDir']) 
            ? self::$config['application']['jsDir'] 
            : __DIR__ . '/../Ice/Static/js/';
        return $dir . $pack . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Получить папку ccs'ок
     * 
     * @param type $pack
     * @return type
     */
    public function getCssDir($pack = 'noPack')
    {
        $dir = isset(self::$config['application']['cssDir']) 
            ? self::$config['application']['cssDir'] 
            : __DIR__ . '/../Ice/Static/css/';
        return $dir . $pack . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Получить папку классов
     * 
     */
    public function getClassDir()
    {
        return isset(self::$config['application']['classesDir']) 
            ? self::$config['application']['classesDir'] 
            : __DIR__ . '/../Ice/Class/';
    }
    
    /**
     * Получить папку классов
     * 
     */
    public function getHelpersDir()
    {
        return isset(self::$config['application']['helpersDir']) 
            ? self::$config['application']['helpersDir'] 
            : __DIR__ . '/../Ice/Class/Helper/';
    }
    
    /**
     * Получить папку классов
     * 
     */
    public function getServicesDir()
    {
        return isset(self::$config['application']['servicesDir']) 
            ? self::$config['application']['servicesDir'] 
            : __DIR__ . '/../Ice/Service/';
    }
    
    
    
}
