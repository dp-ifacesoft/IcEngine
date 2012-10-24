<?php

abstract class UOW_Query_Abstract
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
	 */
	abstract public function push(Query_Abstract $query);
}