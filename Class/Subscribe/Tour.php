<?php

class Subscribe_Tour extends Subscribe_Abstract
{
	public $config = array (
		'From' => array (
			'email' => 'tours@vipgeo.ru',
			'name' => 'Vipgeo.ru'
		),
		'Subject' => 'Рассылка горячих туров'
	);

	public function get ($City__id)
	{
		$collection = new Tour_Hot_Collection ();
		return $collection
			->addOptions (array (
				'name' => 'city',
				'City__id' => $City__id

			))
			->items ();
	}
}