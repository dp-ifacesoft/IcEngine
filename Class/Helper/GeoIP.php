<?php
/**
 * @desc Класс для работы с Гео-айпи
 * @author Илья Колесников
 * @package IcEngine
 */

class Helper_GeoIP
{

	public static function ip2int ($ip)
	{
		$ip = explode ('.', $ip);
		return $ip [3] + 256 * ($ip [2] + 256 * ($ip [1] + 256 * $ip [0]));
		//return $ip [3] + 256 * $ip [2] + 65536 * $ip [1] + 16777216 * $ip [0];
	}

	/**
	 * @desc Получить город, которому соответствует
	 * IP адрес текущего пользователя
	 * @return City
	 */
	public static function getCity ($ip = null)
	{
		$ip = self::ip2int ($ip !== null ? $ip : Request::ip ());

		$city_name = DDS::execute (
			Query::instance ()
				->select ('net_city.name_ru AS name')
				->from ('`net_ru`')
				->from ('`net_city`')
				->where ('net_ru.begin_ip<=?', $ip)
				->where ('net_ru.end_ip>=?', $ip)
				->where ('net_ru.city_id=net_city.id')
		)->getResult ()->asValue ();

		return Model_Manager::byQuery (
			'City',
			Query::instance ()
				->where ('name', $city_name)
		);

		$query = Query::instance ()
			->select ('city_id')
			->from ('Net_City_Ip')
			->where ('begin_ip<=?', $ip)
			->where ('end_ip>=?', $ip);

		$city_id = DDS::execute ($query)
			->getResult ()
				->asValue ();

		return Model_Manager::byKey (
			'City',
			$city_id
		);
	}
}