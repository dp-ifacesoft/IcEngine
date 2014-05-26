<?php

/**
 * Менеджер плагинов модулей
 *
 * @author morph
 */
class Module_Plugin_Manager
{
    /**
     * Выход плагинов
     *
     * @var array
     */
    protected static $output = array();

    /**
     * Добавить выход на позицию
     *
     * @param string $position
     * @param string $html
     */
    public static function addOutput($position, $html)
    {
        self::$output[$position][] = $html;
    }

    /**
     * Получить выход плагинов для позиции
     *
     * @param string $name
     * @return array
     */
    public static function forPosition($name)
    {
        return isset(self::$output[$name]) ? self::$output[$name] : array();
    }

    /**
     * Получить плагины для модуля
     *
     * @param string $moduleName
     * @return Module_Plugin_Collection
     */
    public static function getFor($moduleName)
    {
        $module = Model_Manager::byOptions(
            'Module',
            array(
                'name'  => '::Name',
                'value' => $moduleName
            )
        );
        if (!$module) {
            return array();
        }
        $pluginCollection = Model_Collection_Manager::create('Module_Plugin')
            ->addOptions(
                '::Active',
                array(
                    'name'  => 'Module',
                    'id'    => $module->key()
                )
            );
        return $pluginCollection;
    }
}