<?php

/**
 * Dto для Model_Create
 *
 * @author morph
 */
class Model_Create_Dto extends Dto
{
    /**
     * @inheritdoc
     */
    protected static $defaults = array(
        // Название модели
        'modelName'                => '',
        // Автор
        'author'                   => '',
        // Комментарии
        'comment'                  => '',
        // Поля
        'fields'                   => '',
        // Без создания таблицы
        'withoutTable'             => false,
        // Наследование
        'extends'                  => 'Model'
    );
}