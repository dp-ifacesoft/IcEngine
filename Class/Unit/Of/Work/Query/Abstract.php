<?php

/**
 * Абстрактная модель запроса
 *
 * @author neon
 */
abstract class Unit_Of_Work_Query_Abstract
{
	/**
	 * Собирает запрос
	 *
	 * @param string $key
	 * @param array $data
	 */
	abstract public function build($key, $data);

	/**
	 * Отправляет raw в буфер
	 *
	 * @param Query_Abstract $query
	 * @param Model $object
	 * @param string $loader
	 */
	abstract public function push(Query_Abstract $query, $object = null, $loader = null);

	public function pushRaw($uniqName, $object, $wheres)
	{
		$locator = IcEngine::serviceLocator();
		$unitOfWork = $locator->getService('unitOfWork');
		$unitOfWork->pushRaw(QUERY::SELECT, $uniqName, array(
			'object'	=> &$object,
			'wheres'	=> $wheres
		));
	}

}