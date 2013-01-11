<?php

/**
 * Парсер логинзы для yandex.ru
 *
 * @author neon
 */
class Loginza_Parser_Yandex extends Loginza_Parser_Abstract
{
	/**
	 * @see Loginza_Parser_Abstract::parse
	 */
	public function parse($loginza)
	{
		$data = $loginza->data;
        var_dump($data);
        print_r($loginza);die;
		$result = array(
			'email'			=> $data['email'],
			'name'			=> $data['name']['full_name'],
			'surname'		=> '',
			//'Country__id'	=> 55,
			'birthDate'		=> isset($data['dob'])
				?  $data['dob'] . ' 00:00:00'
				: Helper_Date::NULL_DATE,
			'Sex__id'		=> isset ($data['gender'])
				? ($data['gender'] == 'F' ? 2 : 1)
				: 0
		);
		return $result;
	}
}