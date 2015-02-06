<?php
/**
 * Построитель Html_Form'ы
 *
 * @author LiverEnemy
 *
 * @Service("htmlFormManager")
 */

class Html_Form_Manager extends Manager_Simple
{
    /**
     * @inheritdoc
     * @return Html_Form
     */
    public function get($name = '', $default = NULL)
    {
        if (empty($name)) {
            return new Html_Form();
        }
        return parent::get($name, $default);
    }
}