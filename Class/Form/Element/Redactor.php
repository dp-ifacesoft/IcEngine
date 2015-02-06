<?php

/**
 * Форма типа Redactor
 *
 * @author markov
 */
class Form_Element_Redactor extends Form_Element
{
    public function __construct() 
    {
        $this->setData(array(
            'template'  => 'admin'
        ));
    }
}
