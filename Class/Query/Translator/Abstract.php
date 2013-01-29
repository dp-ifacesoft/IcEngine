<?php

/**
 * @desc Абстракный транслятор запросов
 * @author goorus, morph
 */
class Query_Translator_Abstract
{
	/**
	 * @desc Возвращает имя транслятора
	 * @return string
	 */
	public function getName ()
	{
		return substr (
			get_class ($this),
			strlen ('Query_Translator_')
		);
	}

	/**
	 * @desc Транслирует запрос.
	 * @param Query_Abstract $query Запрос.
	 * @return mixed Результат трансляции.
	 */
	public function translate (Query_Abstract $query)
	{
		$type = $query->getName ();
		$parts = explode ('_', $type);
		foreach ($parts as &$part)
		{
			$part = substr (strtoupper ($part), 0, 1).
				substr (strtolower ($part), 1);
		}
		$type = implode ('', $parts);
		return call_user_func (
			array ($this, '_render' . $type),
			$query
		);
	}
}