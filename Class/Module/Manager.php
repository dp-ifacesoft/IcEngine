<?php

/**
 * @desc Менеджер модулей же
 * @author morph
 */
class Module_Manager extends Manager_Abstract
{
	/**
	 * @desc Добавить модуль в проект
	 * @param string $module_name Название модуля
	 */
	public static function addModule ($module_name)
	{
		$module_dir = IcEngine::root () . $module_name . '/';
		Loader::addPath ('Class', $module_dir . 'Class/');
		Loader::addPath ('Class', $module_dir . 'Model/');
		Loader::addPath ('Class', $module_dir);
		Loader::addPath ('Controller', $module_dir . 'Controller/');
		Loader::addPath ('includes', $module_dir . 'includes/');
		Config_Manager::addPath ($module_dir . 'Config/');
		$view = View_Render_Manager::getView ();
		$view->addTemplatesPath ($module_dir . 'View');
	}
}