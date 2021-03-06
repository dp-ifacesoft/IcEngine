<?php
/**
 *
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
        $result = 0;
        if (!$paths) {
            return $result;
        }
        foreach ($paths as $path) {
			$hasFailed = false;
            try {
                unlink(IcEngine::root() . $path);
            } catch (Exception $ex) {
				$hasFailed = true;
                $debug = $this->getService('debug');
                $debug->log($ex->getMessage(), 'user');
            }
			$result = $hasFailed ? $result : $result++;
        }
		return $result;
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
        return $this->makeDir($fullPathName, $mode, true);
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
        return $this->makeDir($fullPathName, $mode, true);
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
        return $this->makeDir($fullPathName, $mode, true);
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
        return $this->makeDir($fullPathName, $mode, true);
    }
    
    /**
     * Добавить хелпер
     * 
     * @param string $name
     * @return bool
     */
    public function makeHelperDir($name, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToHelper($name, false);
        return $this->makeDir($fullPathName, $mode, true);
    }
    
    /**
     * Добавить сервис
     * 
     * @param string $name
     * @return bool
     */
    public function makeServiceDir($name, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToService($name, false);
        return $this->makeDir($fullPathName, $mode, true);
    }
    
    /**
     * Добавить цсс
     * 
     * @param string $name
     * @return bool
     */
    public function makeClassDir($name, $mode = 0755)
    {
        $fullPathName = $this->getFullPathToClass($name, false);
        return $this->makeDir($fullPathName, $mode, true);
    }
    
    /**
     * Создать файл по имени класса
     * 
     */
    public function makeDir($path, $mode = 0755, $recursive = true)
    {
        return is_dir($path) 
            ?: mkdir($path,$mode, $recursive);
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
        $fileName = $dir . $name . '.php';
        return $includeFileName ? $fileName : dirname($fileName);
    }
}