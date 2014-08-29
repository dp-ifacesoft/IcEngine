<?php

/**
 * Парсер логинзы для facebook.com
 * 
 * @author morph
 */
class Loginza_Parser_Facebook extends Loginza_Parser_Abstract
{
    /**
     * @inheritdoc
     */
    public function parse($data)
    {
        $result = array(
            'email'			=> $data['email'],
            'name'			=> $data['name']['full_name'],
            'surname'		=> '',
            'birthDate'		=> isset ($data['dob'])
                ? $data['dob'] . ' 00:00:00'
                : '0000-00-00 00:00:00',
            'Sex__id'		=> isset($data['gender'])
                ? ($data['gender'] == 'F' ? 2 : 1)
                : 0
        );
        return $result;
    }
}