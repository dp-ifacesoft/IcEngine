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
     * @Role("Cli")
     * @Template(null)
     */
    public function vo($name)
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
        file_put_contents($filename, $output);
    }
    
    /**
     * Создать контроллер
     * 
     * @param type $name
     * @param type $indexAction
     * @Role("Cli")
     * @Template(null)
     */
    public function controller($name, $indexAction = TRUE)
    {
        if($indexAction) {
            App::controllerManager()->call($this->name(), 'action', [
                'name'              =>  'index',
                'controllerName'    =>  $name
            ]);
        }
    }
    
    /**
     * Создать экшн
     * 
     * @param string $name имя экшена
     * @param string $controllerName имя контроллера
     * @param boolean $view включать ли вьюшку
     * @param boolean $css включать ли цсску
     * @param boolean $js включать ли джэсочку
     * @Role("Cli")
     * @Template(null)
     */
    public function action($name, $controllerName, $view = TRUE, $css = TRUE, $js = TRUE)
    {
        if($view) {
            App::controllerManager()->call($this->name(), 'view', [
                'controllerName'    =>  $controllerName,
                'actionName'        =>  $name
            ]);
        }
    }
    
    /**
     * Создать шаблон
     * 
     * @param type $controllerName
     * @param type $actionName
     * @Role("Cli")
     * @Template(null)
     */
    public function view($controllerName, $actionName = 'index')
    {
        
    }
    
    /**
     * Создать javascript
     * 
     * @param type $controllerName
     * @param type $actionName
     * @Role("Cli")
     * @Template(null)
     */
    public function js($controllerName, $actionName = 'index')
    {
        
    }
    
    /**
     * Создать css
     * 
     * @param type $controllerName
     * @param type $actionName
     * @Role("Cli")
     * @Template(null)
     */
    public function css($controllerName, $actionName = 'index')
    {
        
    }
    
    /**
     * Создать сервис
     * 
     * @param type $name
     * @param type $methodName
     * @Role("Cli")
     * @Template(null)
     */
    public function service($name, $methodName)
    {
        if($methodName) {
            App::controllerManager()->call($this->name(), 'method', [
                'serviceName'    =>  $name,
                'methodName'        =>  $methodName
            ]);
        }
    }
    
    /**
     * Создать метод
     * 
     * @param type $serviceName
     * @param type $methodName
     * @Role("Cli")
     * @Template(null)
     */
    public function method($serviceName, $methodName)
    {
        
    }
    
    
    /**
     * Справка
     * 
     * @param string $command команда
     * @Template(null)
     * @Role("Cli")
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
     * @Role("Cli")
     * @Template(null)
     */
    public function index($command)
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
     * @Role("Cli")
     * @Template(null)
     */
    public function analyze($name, $method)
    {
        
    }
    
}
