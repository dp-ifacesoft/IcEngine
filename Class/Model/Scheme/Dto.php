<?php

/**
 * Dto для Model_Scheme
 *
 * @author morph
 */
class Model_Scheme_Dto extends Dto
{
    /**
     * @inheritdoc
     */
    protected static $defaults = array(
        // Автор
        'author'                => '',
        // Комментарий к модели
        'comment'               => '',
        // Поля
        'fields'                => array(
            'id'    => array(
                'Int', array(
                    'Size'  => 11,
                    'Not_Null',
                    'Auto_Increment'
                )
            )
        ),
        // Индексы
        'indexes'                  => array(
            'id'    => array(
                'Primary', array('id')
            )
        ),
        // Связи
        'references'                => array(),
        // Настройка админки
        'admin'                     => array(),
        // Язяковая схема
        'languageScheme'            => array(),
        // Схема для генерации
        'createScheme'              => array()
    );
}