<?php

/**
 * Парсер +google для логинзы
 * 
 * @author morph
 */
class Loginza_Parser_Google extends Loginza_Parser_Facebook
{
    /**
     * @inheritdoc
     */
    public function parse($data)
    {
        $result = array(
            'email'			=> $data['email'],
            'name'			=> $data['name']['first_name'],
            'surname'		=> $data['name']['last_name'],  
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