<?php

/**
 * Генератор всего
 *
 * @author markov, apostle
 */
class Controller_Create extends Controller_Abstract
{
    /**
     * Генерирует класс vo с геттерами
     * 
     * @param string $name название класса
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function vo($name = false)
    {
        $nameClass = 'Vo_' . $name;
        $config = App::configManager()->get($nameClass);
        if (!$config) {
            echo 'Конфиг Vo не найден';
            return false;
        }
        $resultFields = [];
        if (isset($config['fields'])) {
            foreach ($config['fields']->__toArray() as $key => $field) {
                if (is_array($field)) {
                    $resultFields[$key] = $field;
                } else {
                    $resultFields[$field] = [];
                }
            }
        }
        $output = App::helperCodeGenerator()->fromTemplate(
            'vo', [
                'name'      => $nameClass,
                'comment'   => $config['comment'] ? $config['comment'] : null, 
                'author'   => $config['author'] ? $config['author'] : null,
                'fields'    => $resultFields,
            ]
        );
        $filename = IcEngine::root() . 'Ice/Class/' . str_replace('_', '/', $nameClass) . '.php';
        if (App::helperFile()->makeDir($filename)) {
            file_put_contents($filename, $output);
        }
    }
    
    /**
     * Создать контроллер
     * 
     * @param type $name
     * @param type $indexAction
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function controller($name, $indexAction = false, $comment = null)
    {
        $controllerNameWithPrefix = 
                App::helperClass()->getControllerName($name);
        if (class_exists($controllerNameWithPrefix)) {
            App::helperCli()->printLine('Контроллер уже существует');
        } else {
            $output = App::helperCodeGenerator()->fromTemplate(
                'controller', [
                    'name'      => App::helperClass()->getClassNameWithoutPrefix('Controller_', $name),
                    'comment'   => $comment,
                    'author'    => IcEngine::getAuthor(),
                    'date'      => App::helperDate()->toUnix()
                ]
            );
            App::helperFile()->makeControllerDir($name);
            $path = App::helperFile()->getFullPathToController($name);
            file_put_contents($path, $output);
        }
        if($indexAction) {
            App::controllerManager()->call($this->name(), 'action', [
                'name'              =>  'index',
                'controller'        =>  App::helperClass()->getControllerName($name, false),
                'view'              =>  false,
                'css'               =>  false,
                'js'                =>  false,
            ]);
        }
    }
    
    /**
     * Создать экшн
     * 
     * @param string $name имя экшена
     * @param string $controller имя контроллера
     * @param boolean $view включать ли вьюшку (по умолчанию true)
     * @param boolean $css включать ли цсску
     * @param boolean $js включать ли джэйэсочку
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function action($name, $controller, $comment = null, 
            $arguments = null, $view = true, $css = true, $js = true
    ) {
        $controllerNameWithPrefix = 
                App::helperClass()->getControllerName($controller);
        if (!class_exists($controllerNameWithPrefix)) {
            App::helperCli()->confirm(
                'Контроллера "'. $controllerNameWithPrefix 
                    . '" не существует, создать? [yes/no]',
                'yes',
                function() use ($controllerNameWithPrefix){
                    App::controllerManager()->call($this->name(), 'controller', [
                        'name'              =>  $controllerNameWithPrefix,
                        'indexAction'       =>  false,
                    ]);
                },
                function() {
                    die;
                }
            );
        }
        $class = new ReflectionClass($controllerNameWithPrefix);
        if ($class->hasMethod($name)) {
            App::helperCli()->printLine('Метод "' . $name . '" уже существует.');
            return;
        }
        $output = App::helperCodeGenerator()->fromTemplate(
            'action', [
                'name'      => $name,
                'comment'   => $comment,
                'arguments' => $arguments
            ]
        );
        App::helperClass()->insertTextMethod($controllerNameWithPrefix, $output);
        if($view) {
            App::controllerManager()->call($this->name(), 'view', [
                'controllerName'    =>  $controller,
                'actionName'        =>  $name
            ]);
        }
        if($css) {
            App::controllerManager()->call($this->name(), 'css', [
                'controllerName'    =>  $controller,
                'actionName'        =>  $name
            ]);
        }
        if($js) {
            App::controllerManager()->call($this->name(), 'js', [
                'controllerName'    =>  $controller,
                'actionName'        =>  $name
            ]);
        }
    }
    
    /**
     * Создать шаблон
     * 
     * @param string $controller имя контроллера
     * @param string $action имя действия (по умолчанию index)
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function view($controller, $action = 'index')
    {
        $fullpath = App::helperFile()->getFullPathToView($controller, $action);
        if (file_exists($fullpath)) {
            App::helperCli()->printLine('View уже существует');
            return;
        }
        $output = App::helperCodeGenerator()->fromTemplate(
            'view', []
        );
        App::helperFile()->makeViewDir($controller, $action);
        $path = App::helperFile()->getFullPathToView($controller, $action);
        file_put_contents($path, $output);
    }
    
    /**
     * Создать javascript
     * 
     * @param string $controller имя контроллера
     * @param string $action имя действия (по умолчанию index)
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function js($controller, $action = 'index')
    {
        $fullpath = App::helperFile()->getFullPathToJs($controller, $action);
        if (file_exists($fullpath)) {
            App::helperCli()->printLine('Js уже существует');
            return;
        }
        $output = App::helperCodeGenerator()->fromTemplate(
            'js', [
                'controller'    =>  $controller,
                'action'        =>  ucfirst($action)
            ]
        );
        App::helperFile()->makeJsDir($controller, $action);
        $path = App::helperFile()->getFullPathToJs($controller, $action);
        file_put_contents($path, $output);
    }
    
    /**
     * Создать css
     * 
     * @param string $controller имя контроллера
     * @param string $action имя действия (по умолчанию index)
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function css($controller, $action = 'index')
    {
        $fullpath = App::helperFile()->getFullPathToCss($controller, $action);
        if (file_exists($fullpath)) {
            App::helperCli()->printLine('Css уже существует');
            return;
        }
        $output = App::helperCodeGenerator()->fromTemplate(
            'css', []
        );
        App::helperFile()->makeCssDir($controller, $action);
        $path = App::helperFile()->getFullPathToCss($controller, $action);
        file_put_contents($path, $output);
    }
    
    /**
     * Создать сервис
     * 
     * @param string $name имя сервиса
     * @param string $method имя метода
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function service($name, $method = false, $comment = null)
    {
        $serviceNameWithPrefix = 
                App::helperClass()->getServiceName($name);
        $annotationServiceName = App::helperService()->makeAnnotationName($serviceNameWithPrefix);
        if (class_exists($serviceNameWithPrefix)) {
            App::helperCli()->printLine('Сервис уже существует');
            return;
        } else {
            $annotationServiceName = App::helperService()->makeAnnotationName($serviceNameWithPrefix);
            if (App::helperService()->isAnnotationExists($annotationServiceName)) {
                App::helperCli()->printLine('Аннотация с именем "' . $annotationServiceName . '"уже существует');
                return;
            }
            $output = App::helperCodeGenerator()->fromTemplate(
                'serviceList', [
                    'name'          => App::helperClass()->getClassNameWithoutPrefix('Service_', $name),
                    'comment'       => $comment,
                    'author'        => IcEngine::getAuthor(),
                    'date'          => App::helperDate()->toUnix(),
                    'serviceName'   =>  $annotationServiceName
                ]
            );
            App::helperFile()->makeServiceDir($name);
            $path = App::helperFile()->getFullPathToService($name);
            file_put_contents($path, $output);
        }
        if ($method) {
            App::controllerManager()->call($this->name(), 'method', [
                'serviceName'    =>  $name,
                'methodName'        =>  $method
            ]);
        }
    }
    
    /**
     * Создать метод
     * 
     * @param string $name имя медота
     * @param string $service имя сервиса
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function method($name, $service, $comment = null, $arguments = null)
    {
        $serviceNameWithPrefix = 
                App::helperClass()->getServiceName($service);
        if (!class_exists($serviceNameWithPrefix)) {
            App::helperCli()->confirm(
                'Сервиса "'. $serviceNameWithPrefix 
                    . '" не существует, создать? [yes/no]',
                'yes',
                function() use ($serviceNameWithPrefix){
                    App::controllerManager()->call($this->name(), 'service', [
                        'name'              =>  $serviceNameWithPrefix,
                        'indexAction'       =>  false,
                    ]);
                },
                function() {
                    die;
                }
            );
        }
        $class = new ReflectionClass($serviceNameWithPrefix);
        if ($class->hasMethod($name)) {
            App::helperCli()->printLine('Метод "' . $name . '" уже существует.');
            return;
        }
        $output = App::helperCodeGenerator()->fromTemplate(
            'action', [
                'name'      => $name,
                'comment'   => $comment,
                'arguments' => $arguments
            ]
        );
        App::helperClass()->insertTextMethod($serviceNameWithPrefix, $output);
    }
    
    
    /**
     * Справка
     * 
     * @param string $command команда
     * @Template(null)
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function help($command = null)
    {
        App::controllerManager()->call('Help', 'index', [
            'name'      =>  'Controller_Create',
            'method'    =>  $command
        ]);
    }
    
    /**
     * Действие по умолчанию
     * 
     * @Template(null)
     * @param string $command команда
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function index($command = null)
    {
        App::controllerManager()->call('Help', 'index', [
            'name'      =>  'Controller_Create',
            'method'    =>  $command
        ]);
    }
    
    /**
     * Провести анализ и создать сервисы и хелперы
     * 
     * @Usage("analyze --className")
     * @param string $name имя класса 
     * @param string $method имя метода
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function analyze($class, $method = null)
    {
        $missingServices = 
                App::serviceCodeAnalyze()->getMissingServicesByClass($class);
        if (!$missingServices) {
            App::helperCli()->printLine('все хорошо:)');
            return;
        }
        foreach ($missingServices as $service) {
            App::helperCli()->confirm(
                'нет сервиса ' . $service . '. Создать?[y/n]', 'y', 
                function() use ($service){
                    $serviceName = 
                        App::helperService()->makeNameByAnnotation($service);
                    App::createClassStrategyManager()->create([
                        'name' =>   $serviceName
                    ]);
                }
            );
        }
    }
    
    /**
     * создать хелпер
     * 
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function helper($name, $method = null, $comment = null)
    {
        $helperNameWithPrefix = 
        App::helperClass()->getHelperName($name);
        $annotationServiceName = App::helperService()->makeAnnotationName($helperNameWithPrefix);
        if (class_exists($helperNameWithPrefix)) {
            App::helperCli()->printLine('Хелпер уже существует');
            return;
        } else {
            $annotationServiceName = App::helperService()->makeAnnotationName($helperNameWithPrefix);
            if (App::helperService()->isAnnotationExists($annotationServiceName)) {
                App::helperCli()->printLine('Аннотация с именем "' . $annotationServiceName . '"уже существует');
                return;
            }
            $output = App::helperCodeGenerator()->fromTemplate(
                'helper', [
                    'name'          => App::helperClass()->getClassNameWithoutPrefix('Helper_', $name),
                    'comment'       => $comment,
                    'author'        => IcEngine::getAuthor(),
                    'date'          => App::helperDate()->toUnix(),
                    'serviceName'   =>  $annotationServiceName
                ]
            );
            App::helperFile()->makeHelperDir($name);
            $path = App::helperFile()->getFullPathToHelper($name);
            file_put_contents($path, $output);
        }
        if ($method) {
            App::controllerManager()->call($this->name(), 'method', [
                'serviceName'    =>  $name,
                'methodName'        =>  $method
            ]);
        }
    }
    
    /**
     * создать класс
     * 
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function simpleClass($name, $method = null, $comment = null)
    {
        $classNameWithPrefix = 
        App::helperClass()->getClassNameWithPrefix('', $name);
         $annotationServiceName = App::helperService()->makeAnnotationName($classNameWithPrefix);
         if (class_exists($classNameWithPrefix)) {
            App::helperCli()->printLine('Класс уже существует');
            return;
        } else {
            $annotationServiceName = App::helperService()->makeAnnotationName($classNameWithPrefix);
            if (App::helperService()->isAnnotationExists($annotationServiceName)) {
                App::helperCli()->printLine('Аннотация с именем "' . $annotationServiceName . '"уже существует');
                return;
            }
            $output = App::helperCodeGenerator()->fromTemplate(
                'class', [
                    'name'          => App::helperClass()->getClassNameWithoutPrefix('', $name),
                    'comment'       => $comment,
                    'author'        => IcEngine::getAuthor(),
                    'date'          => App::helperDate()->toUnix(),
                    'serviceName'   =>  $annotationServiceName
                ]
            );
            App::helperFile()->makeClassDir($name);
            $path = App::helperFile()->getFullPathToClass($name);
            file_put_contents($path, $output);
        }
        if ($method) {
            App::controllerManager()->call($this->name(), 'method', [
                'serviceName'    =>  $name,
                'methodName'        =>  $method
            ]);
        }
    }
}
