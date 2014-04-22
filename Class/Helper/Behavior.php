<?php

/**
 * Хелпер для смены окружения
 * 
 * @author morph
 * @Service("helperBehavior")
 */
class Helper_Behavior
{
    /**
     * Путь к файлу окружения
     */
    const PATH = 'Ice/Var/Helper/Site/Location.txt';
    
    /**
     * Привести строку с названием окружения к нормальному виду
     * 
     * @param string $str
     * @return string
     */
    protected function clear($str)
    {
        return str_replace(array("\t", "\n", "\r"), '', trim($str));
    }
    
    /**
     * Получить окружение
     * 
     * @return string
     */
    public function get()
    {
        $path = $this->getPath();
        if (!is_file($path)) {
            return null;
        }
        $behavior = file_get_contents($path);
        return $this->clear($behavior);
    }
    
    /**
     * Получить путь до окружения
     * 
     * @return string
     */
    public function getPath()
    {
        return IcEngine::root() . self::PATH;
    }
    
    /**
     * Изменить окружение
     * 
     * @param string $behavior
     */
    public function set($behavior)
    {
        $path = $this->getPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $behavior);
    }
}