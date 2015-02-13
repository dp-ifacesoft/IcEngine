<?php
/**
 * Абстрактный фильтр-галочка
 *
 * @author LiverEnemy
 */

abstract class Model_Collection_Filter_Checkbox extends Model_Collection_Filter_Text
{
    /**
     * @inheritdoc
     */
    protected $_type = 'checkbox';

    /**
     * @inheritdoc
     */
    protected function _setValue($value)
    {
        return parent::_setValue((bool) $value);
    }
} 