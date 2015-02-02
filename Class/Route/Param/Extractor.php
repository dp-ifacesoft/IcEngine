<?php
/**
 * Абстрактный класс экстрактора параметров маршрута (роута)
 *
 * Extract (англ.) - извлекать. Дочерние классы должны брать данные из параметра $data и извлекать из них
 * параметры, требуемые для составления конкретного маршрута
 *
 * @see Service_Route::createUrl()
 *
 * @author LiverEnemy
 */

abstract class Route_Param_Extractor
{
    /**
     * Извлечь параметры маршрута к экшену контроллера
     *
     * @param string $action Название требуемого экшена контроллера
     * @param array $data    Данные для извлечения параметров маршрута
     *
     * @return array
     * @throws Exception
     */
    public function call($action, array $data = [])
    {
        if (!is_string($action)) {
            throw new Exception(__METHOD__ . ' requires an action param to be string');
        }
        $method = '_action' . ucfirst($action);
        if (!method_exists($this, $method)) {
            throw new Exception(get_class($this) . ' does not have a method for ' . $action);
        }
        $result = $this->$method($data);
        if (!is_array($result)) {
            throw new Exception(get_class($this) . '::' . $action . '() did not return an array');
        }
        return $result;
    }
} 