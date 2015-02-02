<?php
/**
 * Менеджер полей Html-формы
 *
 * @author LiverEnemy
 *
 * @Service("htmlFormFieldManager")
 */

class Html_Form_Field_Manager extends Manager_Simple
{
    /**
     * @inheritdoc
     * @return Html_Form_Field
     */
    public function get($name, $default = null)
    {
        return parent::get($name, $default);
    }

    /**
     * Получить полное имя класса Html-поля формы по атрибуту $name
     *
     * @param string $name Часть имени класса
     * @return string
     */
    public function getClassName($name)
    {
        $className = get_class($this);
        $managerPos = strrpos($className, '_Manager');
        $plainName = substr($className, 0, $managerPos);
        return $plainName . '_' . ucfirst($name);
    }
}