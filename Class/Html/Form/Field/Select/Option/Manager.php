<?php
/**
 * Менеджер опшенов для полей Html_Form_Field_Select
 *
 * @author LiverEnemy
 *
 * @Service("htmlFormFieldSelectOptionManager")
 */

class Html_Form_Field_Select_Option_Manager extends Manager_Simple
{
    /**
     * @inheritdoc
     * @return Html_Form_Field_Select_Option
     */
    public function get($name = '', $default = NULL)
    {
        if (empty($name)) {
            return new Html_Form_Field_Select_Option();
        }
        return parent::get($name, $default);
    }
}