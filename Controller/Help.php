<?php

/**
 * Контроллер справки
 * 
 * @author apostle
 */
class Controller_Help extends Controller_Abstract
{
    /**
     * Вывести справку по классу/функции
     * 
     * @param string $name имя класса
     * @param string $method имя метода класса
     * @Template(null)
     */
    public function index($name, $method)
    {
        if (!$name) {
            $this->index(__CLASS__, __FUNCTION__); 
            return;
        }
        $class = new ReflectionClass($name);
        $methods = [];
        if (!$method) {
            $allMethods = $class->getMethods();
            foreach($allMethods as $method) {
                if($method->class == $class->getName()) {
                    $methods[] = $method;
                }
            }
            
        } else {
            $methods[] = $class->getMethod($method);
        }
        foreach ($methods as $method) {
            App::helperCli()->fillLine('-');
            App::helperCli()->printLine(App::helperCli()->bold($method->getName()));
            App::helperCli()->printLine(
                    App::helperClass()
                        ->getDescription($class->getName(), $method->getName())
            );
            App::helperCli()->printLine(
                'Usage: ./ic ' . $class->getName() . '/' . $method->getName() 
                    . App::helperClass()->getParamsCli(App::helperClass()
                        ->getParams($class->getName(), $method->getName())
            ));
            App::helperCli()->fillLine('-');  
        }
    }
}
