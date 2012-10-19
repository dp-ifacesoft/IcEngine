<?php

class Component_Geo_Point extends Model_Component
{

	/**
	 * @desc Вовзращает название стиля
	 * @return string
	 */
	public function style ()
	{
		return $this->Geo_Point_Style->name;
	}

	/**
	 * @desc Для передачи в Js Resource_Manager
	 * @return array
	 */
	public function toJs ()
	{
		return array (
			'id'		=> $this->key (),
			'longitude'	=> $this->longitude,
			'latitude'	=> $this->latitude,
			'title'		=> $this->name
		);
	}

}