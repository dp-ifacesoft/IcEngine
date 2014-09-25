<?php

/**
 * Описание File
 *
 * @author Apostle
 * @Service("helperFile")
 */
class Helper_File extends Helper_Abstract
{
    /**
     * Удаляет файлы по путям
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
