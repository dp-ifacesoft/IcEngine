<?php
/**
 * Абстрактный класс фильтра с выбором одного значения из нескольких
 *
 * @author LiverEnemy
 */

abstract class Model_Collection_Filter_Select extends Model_Collection_Filter_Text
{
    /**
     * @inheritdoc
     */
    protected $_type = 'select';

    /**
     * Возможные значения для выбора
     *
     * @var array
     */
    protected $_values;
    /**
     * Массив возможных значений для выбора
     *
     * Значения должны быть массивами вида ['id' => '...', 'title' => '...', ]
     *
     * @return array
     */
    abstract public function getValues();
} 