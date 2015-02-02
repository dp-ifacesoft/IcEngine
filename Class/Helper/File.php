<?php

/**
 * @desc Помощник работы с файлами
 * @author Юрий, Apostle
 * @package IcEngine
 * @Service("helperFile")
 */
class Helper_File extends Helper_Abstract
{
    
    /**
     * @desc Возвращает расширение файла
     * @param $filename Имя файла
     * @return string Расширение
     */
    public function extention ($filename)
    {
        return strtolower (substr (strrchr ($filename, '.'), 1));
    }
    
    /**
     * Удаляет файлы по путям
     * 
     */
    public function delete($paths) 
    {
        if (!$paths) {
            return;
        }
        
        foreach ($paths as $path) {
            try {
                unlink(IcEngine::root() . $path);
            } catch (Exception $ex) {
                $debug = $this->getService('debug');
                $debug->log($ex->getMessage(), 'user');
            }
        }
    }
    
    /**
     * @desc Получает список файлов или папок в определенной папке (возможно, рекурсивно)
     * @param string $dir путь к папке, в которой осуществлять поиск
     * @param boolean $sortAsc Сортировать результаты по возрастанию
     * @param boolean $recursive осуществлять ли поиск рекурсивно
     * @param boolean $relative возвращать относительные, а не абсолютные, имена найденных элементов
     * @param boolean $includingFiles включать в выборку найденные файлы
     * @param boolean $includingDirs включать в выборку найденные файлы
     * @return Array
     */
    public function scan($dir,
                                $sortAsc = true,
                                $recursive = false,
                                $relative = true,
                                $includingFiles = true,
                                $includingDirs = false)
    {
        $dir = rtrim($dir, '/');
        if (!is_dir($dir))
        {
            return NULL;
        }
        $elements = scandir($dir, !$sortAsc);
        $return = array();
        foreach ($elements as $item)
        {
            if ($item == '.' || $item == '..')
            {
                continue;
            }
            $path = $dir . '/' . $item;
            if (($includingFiles && is_file($path))
                ||
                ($includingDirs  && is_dir($path)))
            {
                $return[] = $relative ? $item : $path;
            }
            if ($recursive && is_dir($path))
            {
                $return = array_merge($return,
                    $this->scan($path, $sortAsc, $recursive, $relative, $includingFiles, $includingDirs));
            }
        }
        return $return;
    }
    
    /**
     * Получить список файлов в директории
     * 
     * @param string $dir директория поиска
     * @param string $basename только имя файла, без пути
     * @param string $filetype тип файла
     * @return array 
     */
    public function getFileList($dir, $basename = false, $filetype = 'php')
    {
        $paths = [];
        $dir = trim($dir, '/');
        $pattern = IcEngine::root() . $dir . '/*.' . $filetype;
        foreach(glob($pattern) as $path) {
            $paths[] = $basename ? basename($path) : $path;
        }
        
        return $paths;
    }
    
    /**
     * Проверить существование файла по конфигу
     * 
     * @param string $filename - путь к файлу 
     * @param array $config - массив путей
     */
    public function fileExists($filename, $config)
    {
        $filenameRelative = preg_replace('#http://.*?/#', '', $filename);
        foreach ($config as $prefix) {
            $path = IcEngine::root() 
                    . trim($prefix, '/') . '/' . ltrim($filenameRelative, '/');
            if (file_exists($path)) {
                return $path;
            }
        }
        return false;
    }
    
    /**
     * Добавить контроллер
     * 
     * @param string $name имя контроллера
     * @return bool
     */
    public function makeControllerDir($name, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToController($name, false);
        return mkdir($fullPathName, $mode, true);
    }
    
      
   
    /**
     * Добавить view
     * 
     * @param string $name
     * @return bool
     */
    public function makeViewDir($controller, $action, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToView(
                App::helperClass()->getControllerName($controller),
                $action, 
                false
        ); 
        return mkdir($fullPathName, $mode, true);
    }
    
    /**
     * Добавить js
     * 
     * @param string $name
     * @return bool
     */
    public function makeJsDir($controller, $action, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToJs(
                App::helperClass()->getControllerName($controller), 
                $action, 
                false
        ); 
        return mkdir($fullPathName, $mode, true);
    }
    
    /**
     * Добавить цсс
     * 
     * @param string $name
     * @return bool
     */
    public function makeCssDir($controller, $action, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToCss(
                App::helperClass()->getControllerName($controller), 
                $action, false
        );
        return mkdir($fullPathName, $mode, true);
    }
    
    /**
     * Добавить цсс
     * 
     * @param string $name
     * @return bool
     */
    public function makeHelperDir($path, $name, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToHelper($path, $name, false);
        return mkdir($fullPathName, $mode, true);
    }
    
    /**
     * Добавить цсс
     * 
     * @param string $name
     * @return bool
     */
    public function makeServiceDir($path, $name, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToService($path, $name, false);
        return mkdir($fullPathName, $mode, true);
    }
    
    /**
     * Создать файл по имени класса
     * 
     */
    public function makeDir($path, $mode = 0755, $recursive = true)
    {
        return is_dir(dirname($path)) ?: mkdir(dirname($path),$mode, $recursive);
    }
    
    /**
     * Получить путь до контроллера
     * 
     * @param type $name
     */
    public function getFullPathToController($name, $includeFileName = true)
    {
        $dir = IcEngine::getControllersDir();
        $pos = strpos($name, 'Controller_');
        if ($pos === 0) {
            $name = substr($name, strlen('Controller_'));
        }
        $name = str_replace('_', '/', $name);
        $fileName = $dir . trim($name, '/') . '.php';
        return $includeFileName ? $fileName : dirname($fileName);
    }
    
    /**
     * Получить путь до view
     * 
     * @param string $path путь (от корня вьюшек)
     * @param string $name имя вьюшки
     */
    public function getFullPathToView($controller, $action, $includeFileName = true)
    {
        $dir = IcEngine::getViewsDir();
        $fileName = $dir . str_replace('_', '/', (App::helperClass()
                ->getControllerName($controller) . '_'. $action)) . '.tpl';
        return $includeFileName ? $fileName : dirname($fileName);
    }
    
    /**
     * Получить путь до css
     * 
     * @param string $path путь (от корня вьюшек)
     * @param string $name имя css
     */
    public function getFullPathToCss($controller, $action, $includeFileName = true, $pack = 'noPack')
    {
        $dir = IcEngine::getCssDir();
        $fileName = $dir . str_replace('_', '/', (App::helperClass()
                ->getControllerName($controller) . '_'. ucfirst($action))) 
                . '.css';
        return $includeFileName ? $fileName : dirname($fileName);
    }
    
    /**
     * Получить путь до css
     * 
     * @param string $path путь (от корня вьюшек)
     * @param string $name имя js
     */
    public function getFullPathToJs($controller, $action, $includeFileName = true, $pack = 'noPack')
    {
        $dir = IcEngine::getJsDir();
        $fileName = $dir . str_replace('_', '/', (App::helperClass()
                ->getControllerName($controller) . '_'. ucfirst($action))) 
                . '.js';
        return $includeFileName ? $fileName : dirname($fileName);
    }
    
    /**
     * Получить путь до хелпера
     * 
     * @param string $path путь (от корня вьюшек)
     * @param string $name имя js
     */
    public function getFullPathToHelper($name, $includeFileName = true)
    {
        $dir = IcEngine::getHelpersDir();
        $pos = strpos($name, 'Helper_');
        if ($pos === 0) {
            $name = substr($name, strlen('Helper_'));
        }
        $name = str_replace('_', '/', $name);
        $fileName = $dir . trim($name, '/') . '.php';
        return $includeFileName ? $fileName : dirname($fileName);
    }
    
    /**
     * Получить путь до css
     * 
     * @param string $path путь (от корня вьюшек)
     * @param string $name имя js
     */
    public function getFullPathToService($name, $includeFileName = true)
    {
        $dir = IcEngine::getServicesDir();
        $pos = strpos($name, 'Service_');
        if ($pos === 0) {
            $name = substr($name, strlen('Service_'));
        }
        $name = str_replace('_', '/', $name);
        $fileName = $dir . trim($name, '/') . '.php';
        return $includeFileName ? $fileName : dirname($fileName);
    }
    
    /**
     * Получить путь до классов
     * 
     * @param string $name имя класса 
     * @param boolean $includeFileName имя js
     */
    public function getFullPathToClass($name, $includeFileName = true)
    {
        if (class_exists($name)) {
            $reflectionClass = new ReflectionClass($name);
            $path = $reflectionClass->getFileName();
            return $includeFileName ? $path : dirname($path);
        }
        $dir = IcEngine::getClassDir();
        $fileName = $dir . trim($path, '/') . $name . '.php';
        return $includeFileName ? $fileName : dirname($fileName);
    }
}
