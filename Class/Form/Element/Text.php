<?php

/**
 * Форма типа Text
 *
 * @author markov
 */
class Form_Element_Text extends Form_Element
{
    public function __construct() {
        $this->setAttribute('type', 'text');
    }
}
