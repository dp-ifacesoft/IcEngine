<?php

/**
 * Сервис анализа кода
 * 
 * @author Apostle
 * @Service("serviceCodeAnalyze")
 */
class Service_Code_Analyze extends Service_Abstract
{
    /**
     *
     * @inheritdoc
     */
    public $config = [
        'patterns'  => [
            "#App::(.*?)\(#",
            "#\->getService\('(.*)\'#"
        ]
    ];
    
    /**
     * Получить сервисы, которые объявлены в коде, но не созданы
     * 
     * @param type $className
     */
    public function getMissingServicesByClass($className, $method = null)
    {
        if (!class_exists($className)) {
            return;
        }
        $reflectionClass = new ReflectionClass($className);
        $methods = [];
        if (isset($method)) {
            $methods[] = $reflectionClass->getMethod($method);
        } else {
            $methods = $reflectionClass->getMethods();
        }
        $filename = App::helperFile()->getFullPathToClass($className);
        $source = file($filename);
        $missingClasses = [];
        foreach($methods as $method) {
            $start_line = $method->getStartLine() - 1; 
            $end_line = $method->getEndLine();
            $length = $end_line - $start_line;
            $lines = array_slice($source, $start_line, $length);
            $missingClasses = array_merge(
                    $missingClasses, $this->getMissingServices($lines)
            );
        }
        return $missingClasses;
    }
    
    /**
     * Найти отсутствующие сервисы в тексте
     * 
     * @param string|array
     */
    public function getMissingServices($text)
    {
        if(!is_string($text) && !is_array($text)) {
            return;
        }
        if (is_array($text)) {
            $text = implode('', $text);
        }
        $services = $this->getServicesByPatten($text, $this->config()->patterns->__toArray());
        $missingServices = array_filter($services, function($service) {
            return !App::helperService()->isAnnotationExists($service);
        });
        return $missingServices;
    }
    
    /**
     * Получить сервисы по паттернам
     * 
     * @param array|string $pattern
     */
    public function getServicesByPatten($text, $patterns)
    {
        if (!is_array($patterns)) {
            $patterns = (array)$patterns;
        }
        $services = [];
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $text, $matches);
            if ($matches[1]) {
                $services = array_merge($services, $matches[1]);
            }
        }
        return array_unique($services);
    }
}