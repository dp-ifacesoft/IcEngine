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
        $pos = strripos($class, '_');
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
    
}
