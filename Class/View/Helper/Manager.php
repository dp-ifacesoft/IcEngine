<?php

/**
 * Менеджер хелперов представления
 *
 * @author Юрий, neon
 * @package IcEngine
 * @Service("viewHelperManager")
 */
class View_Helper_Manager
{
	/**
	 * Подключенные хелперы
	 * @var array <View_Helper_Abstract>
	 */
	protected $helpers;

	/**
	 * Возвращает результат работы хелпера.
	 *
	 * @param string $name Название помощника
	 * @param array $params Параметры, передаваемые помощнику
	 * @return View_Helper_Abstract
	 */
	public function get($name, $params = array())
	{
		if (!isset($this->helpers[$name])) {
			$helperName = 'View_Helper_' . $name;
			$this->helpers[$name] = new $helperName;
		}
		return $this->helpers[$name]->get($params);
	}
}