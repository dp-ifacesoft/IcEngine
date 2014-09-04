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
    public function delete($paths) {
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
}
