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
		$loginzaResult = $loginza->data('result');
		$result = array(
			'email'			=> $loginzaResult['email'],
			'name'			=> $loginzaResult['name']['full_name'],
			'surname'		=> '',
			//'Country__id'	=> 55,
			'birthDate'		=> isset($loginzaResult['dob'])
				?  $loginzaResult['dob'] . ' 00:00:00'
				: Helper_Date::NULL_DATE,
			'Sex__id'		=> isset ($loginzaResult['gender'])
				? ($loginzaResult['gender'] == 'F' ? 2 : 1)
				: 0
		);
		return $result;
	}
}