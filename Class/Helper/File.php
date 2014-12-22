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
     * Проверить существование файла по конфигу
     * @param string $filename - путь к файлу 
     * @param array $config - массив путей
     */
    public function fileExists($filename, $config)
    {
        $filenameRelative = preg_replace('#http://.*?/#', '', $filename);
        foreach ($config as $prefix) {
            $path = IcEngine::root() . trim($prefix, '/') . '/' . ltrim($filenameRelative, '/');
            if (file_exists($path)) {
                return $path;
            }
        }
        return false;
    }
}