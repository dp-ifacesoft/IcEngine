<?php

/**
 * Базовый менеджер сущностей
 *
 * @author morph, neon
 */
class Manager_Simple extends Manager_Abstract
{
    /**
     * Стандратная функция получения эмземпляра по имени
     *
     * @param string $name
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $className = get_class($this);
        $managerPos = strrpos($className, '_Manager');
        $plainName = substr($className, 0, $managerPos);
        $objectClassName = $plainName . '_' . $name;
        if (!class_exists($objectClassName) && $default) {
            $objectClassName = $plainName . '_' . $default;
        }
        if (!class_exists($objectClassName)) {
            return null;
        }
        $object = new $objectClassName;
        return $object;
    }
}