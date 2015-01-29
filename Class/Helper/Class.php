<?php

/**
 * Помощник для работы с классами
 *
 * @author Apostle
 * @Service("helperClass")
 */
class Helper_Class extends Helper_Abstract 
{
    /**
     * получить имя класса без префикса пути
     * @param Class $class экземпляр некого класса
     */
    public function getClassTail($class, $lower = TRUE)
    {
        $pos = strripos($class, '_');
            if ($pos !== false) {
                if ($lower) {
                    return strtolower(substr($class, (int)($pos+1)));
                }
                return substr($class, (int)($pos+1));
            }
        return $class;
    }
    
    /**
     * Получить тип класса
     * 
     * @param string $class тип класса 
     */
    public function getClassType($class)
    {
        $pos = stripos($class, '_');
            if ($pos !== false) {
                return substr($class, 0, (int)($pos));
            }
        return $class;
    }
    
    /**
     * Получить описание класса
     * 
     * @param string $className имя класса
     * @param string $methodName имя метода
     */
    public function getDescription($className, $methodName)
    {
        $class = new ReflectionClass($className);
        if (!$class) {
            return;
        }
        if (!isset($methodName) && 
                !in_array($methodName,$class->getMethods())) {
            $docComment = $class->getDocComment();
        } else {
            $method = $class->getMethod($methodName);
            $docComment = $method->getDocComment();    
        }
        return $this->parseComment($docComment);
    }
    
    
    /**
     * Спарсить коммент
     * 
     * @param string $comment
     */
    public function parseComment($comment)
    {
        $pattern = '#\*+\s+([a-zA-Zа-яА-Я\s\*]+)\b#u';
        preg_match_all($pattern, $comment, $matches);
        if (!isset($matches[1][0])) {
            return '';
        }
        return $matches[1][0];
    }
    
    /**
     * Получить параметры класса
     * 
     * @param string $className имя класса
     * @param string $methodName имя метода
     */
    public function getParams($className, $methodName)
    {
        $class = new ReflectionClass($className);
        if (!$class) {
            return;
        }
        if (!isset($methodName) && 
                !in_array($methodName,$class->getMethods())) {
            $docComment = $class->getDocComment();
        } else {

            $method = $class->getMethod($methodName);
            $docComment = $method->getDocComment();    
        }
        return $this->parseParams($docComment);
    }
    
    /**
     * Спарсить коммент
     * 
     * @param string $comment
     */
    public function parseParams($comment)
    {
        $pattern = '#\*+\s+@param\s+[a-zA-Z\s\|]+\$(.*)?#';
        preg_match_all($pattern, $comment, $matches);
        if (!isset($matches[1][0])) {
            return '';
        }
        return $matches[1];
    }
    
    /**
     * Получить параметры в консольном виде
     * 
     * @param array $params параметры
     */
    public function getParamsCli($params)
    {
        $paramsCli = '';
        $params = is_array($params) ? $params : (array)$params;
        foreach($params as $param) {
            $paramsCli .= ' --' . $param;
        }
        return $paramsCli;
    }
    
    /**
     * Возвращает имя контроллера с префиксом
     * 
     * @param string $name имя класса 
     * @param boolean $prefix дописывать ли Controller_
     */
    public function getControllerName($name, $prefix = true)
    {
        if ($prefix) {
            return $this->getClassNameWithPrefix('Controller_', $name);
        }
        return $this->getClassNameWithoutPrefix('Controller_', $name);
    }
    
    /**
     * Возвращает имя сервиса с префиксом
     * 
     * @param string $name имя класса 
     * @param boolean $prefix дописывать ли Service_
     */
    public function getServiceName($name, $prefix = true)
    {
        if ($prefix) {
            return $this->getClassNameWithPrefix('Service_', $name);
        }
        return $this->getClassNameWithoutPrefix('Service_', $name);
    }
    
    /**
     * Возвращает имя хелпера с префиксом
     * 
     * @param string $name имя класса 
     * @param boolean $prefix дописывать ли Helper_
     */
    public function getHelperName($name, $prefix = true)
    {
        if ($prefix) {
            return $this->getClassNameWithPrefix('Helper_', $name);
        }
        return $this->getClassNameWithoutPrefix('Helper_', $name);
    }
    
    /**
     * Получить имя модели
     * 
     * @param string $name
     * @param boolean $prefix дописывать ли Model_
     */
    public function getModelName($name, $prefix = true)
    {
        if ($prefix) {
            return $this->getClassNameWithPrefix('Model_', $name);
        }
        return $this->getClassNameWithoutPrefix('Model_', $name);
    }
    
    /**
     * Получить имя класса с префиксом
     * 
     * @param string $prefix префикс
     * @param string $name имя класса
     * @param boolean $normalize привести ли к CamelCase
     * @return string
     */
    public function getClassNameWithPrefix($prefix, $name, $normalize = true)
    {
        $normalizedPrefix = $prefix;
        if ($normalize) {
            $normalizedPrefix = ucfirst(strtolower($prefix));
        }
        if (strpos($name, $normalizedPrefix) === 0) {
            return $name;
        }
        return $normalizedPrefix . $name;
    }
    
        /**
     * Получить имя класса с префиксом
     * 
     * @param string $prefix префикс
     * @param string $name имя класса
     * @param boolean $normalize привести ли к CamelCase
     * @return string
     */
    public function getClassNameWithoutPrefix($prefix, $name, $normalize = true)
    {
        $normalizedPrefix = $prefix;
        if ($normalize) {
            $normalizedPrefix = ucfirst(strtolower($prefix));
        }
        if (strpos($name, $normalizedPrefix) === 0) {
            return substr($name, strlen($prefix));
        }
        return $name;
    }
    
    /**
     * Получить абсолютный путь класса по имени
     * 
     * @param string $name имя класса 
     */
    public function getPath($name)
    {
        if (!class_exists($name)) {
            return;
        }
        $class = new ReflectionClass($name);
        return $class->getFileName();
    }
    
    /**
     * Получить абсолютный путь класса без имени файла
     * 
     * @param string $name имя класса 
     */
    public function getDir($name)
    {
        $fullpath = $this->getPath($name);
        return dirname($fullpath) . '/';
    }
    
    /**
     * Вставить метод созданный кодогенератором 
     * 
     */
    public function insertTextMethod($name, $text, $line  = null)
    {
        if(!class_exists($name)) {
            return;
        }
        $path = $this->getPath($name);
        $dir = $this->getDir($name);
        if (!$line) {
            $class = new ReflectionClass($name);
            $line = $class->getEndLine()-1;
        }
        $lines = explode(PHP_EOL, file_get_contents($path));
        array_splice($lines, $line, 0, explode(PHP_EOL, $text));
        file_put_contents($path, implode(PHP_EOL, $lines));
    }
    
}
