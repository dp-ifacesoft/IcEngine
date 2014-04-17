<?php

/**
 * Абстрактный класс загрузчика, для Unit_Of_Work
 *
 * @author neon
 */
abstract class Unit_Of_Work_Loader_Abstract
{
	/**
	 * Загрзука raw данных и разнос их по объектам
	 *
	 * @param string $uniqName
	 * @param array $raw
	 */
	abstract public function load($uniqName, $raw);
}