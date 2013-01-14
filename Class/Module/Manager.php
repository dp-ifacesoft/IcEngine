<?php

/**
 * @desc Менеджер модулей же
 * @author morph
 */
class Module_Manager extends Manager_Abstract
{
	/**
	 * Текущие инициализированные модули
	 *
	 * @var array
	 */
	public static $_modules = array();

	/**
	 * @desc Добавить модуль в проект
	 * @param string $module_name Название модуля
	 */
	public static function addModule ($module_name)
	{
		if (isset(self::$_modules[$module_name])) {
			return;
		}
		$module_dir = IcEngine::root () . $module_name . '/';
		Loader::addPath ('Class', $module_dir . 'Class/');
		Loader::addPath ('Class', $module_dir . 'Model/');
		Loader::addPath ('Class', $module_dir);
		Loader::addPath ('Controller', $module_dir . 'Controller/');
		Loader::addPath ('includes', $module_dir . 'includes/');
		Config_Manager::addPath ($module_dir . 'Config/');
		if ($module_name != 'Ice') {
			Config_Manager::addPath (IcEngine::root() . 'Ice/Config/Module/' .
				$module_name . '/');
		}
		$view = View_Render_Manager::getView ();
		$view->addTemplatesPath ($module_dir . 'View');
		self::$_modules[$module_name] = array();
	}

	public static function init()
	{
		$moduleCollection = Model_Collection_Manager::create(
			'Module'
		);
		$moduleCollection->sort('id DESC');
		foreach ($moduleCollection as $module) {
			self::addModule($module->name);
		}
	}
}