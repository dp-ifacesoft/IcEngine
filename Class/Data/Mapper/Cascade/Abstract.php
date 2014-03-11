<?php
/**
 *
 * @desc Абстрактный мэппер для каскада
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Mapper_Cascade_Abstract extends Data_Mapper_Abstract
{

	/**
	 * @desc Лок ключа.
	 * Если ключ залочен, будет возвращен кэш, даже если истекло время жизни.
	 * @param string $key
	 */
	public function lock ($key)
	{

	}

	/**
	 * @desc Сопоставляет запрос результату.
	 * Может использоваться для кэширования.
	 * @param string $key Ключ запроса.
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @param Query_Result $result
	 */
	public function unlock ($key, Query_Abstract $query, $options, Query_Result $result)
	{

	}

}