<?php

/**
 * Хелпер для работы с сервисами
 * 
 * @author morph
 * @Service("helperService")
 */
class Helper_Service
{
    /**
     * Получить название сервиса по названию класса
     *
     * @param string $name
     * @return string
     */
    public function byClass($name)
    {
        $tmp = explode('_', $name);
        $tmp[0] = strtolower($tmp[0]);
        return implode('', $tmp);
    }
    
    /**
     * Привести имя, написаное вида className в Class_Name
     * 
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        $matches = array();
		$reg_exp = '#([A-Z]*[a-z]+)#';
		preg_match_all($reg_exp, $name, $matches);
		if (empty($matches[1][0])) {
			return $name;
		}
		return implode('_', array_map('ucfirst', $matches[1]));
    }
    
    /**
     * Создать аннотационное имя
     * 
     * @param string $serviceName имя сервиса
     */
    public function makeAnnotationName($serviceName)
    {
        $words = explode('_', $serviceName);
        array_map(function($word){
            return ucfirst(strtolower($word));
        }, $words);
        $words[0] = lcfirst($words[0]);
        return implode('', $words);
    }
    
    /**
     * Создать имя сервиса по аннотации
     * 
     * @param string $annotationName имя сервиса
     */
    public function makeNameByAnnotation($annotationName)
    {
        return ucfirst(preg_replace('#([A-Z])#', '_$1', $annotationName));
    }
    
    /**
     * Проверить, существует ли аннотация
     * 
     * @param string $annotationName имя аннотации сервиса
     */
    public function isAnnotationExists($annotationName)
    {
        $config = App::configManager()->get('Service_Source');
        foreach($config as $serviceName => $classObject) {
            if ($serviceName == $annotationName) {
                return true;
            }
        }
        return false;
    }
}