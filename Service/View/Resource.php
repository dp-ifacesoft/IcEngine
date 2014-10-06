<?php

/**
 * Сервис для работы со статикой
 *
 * @author markov
 */
class Service_View_Resource extends Service_Abstract
{
    /**
     * Получает путь до файла взависимости от модуля
     * 
     * @param String $filename имя файла
     * @param String $type тип файла
     * @return String
     */
    public function getFileNameByGroupDefault($filename, $type) 
    {
        $modules = $this->getService('moduleManager')->getModules();
        foreach (array_keys($modules) as $name) {
            $path = IcEngine::root() . $name . '/Static/' . $type . '/' .  ltrim($filename, '/');
            if (file_exists($path)) {
                return $path;
            }
        }
        return '';
    }
}
