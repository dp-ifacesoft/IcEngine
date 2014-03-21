<?php
/**
 *
 * @desc Диспетчер контроллеров.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Dispatcher
{
	/**
	 * @desc Ищет соответствие имени файла и метода
	 * @param string $controller
	 * @param string $action
	 * @return array<controller,action>
	 */
	public static function dispatch ($controller, $action)
	{
		return array (
			'controller'	=> $controller,
			'action'		=> $action
		);
	}
	
	/**
	 * @desc Запускает цикл диспетчеризации.
	 * Находит соответствиие имени файла и метода
	 * @param Route_Action_Collection $actions
	 * @return Route_Action_Collection
	 */
	public static function loop ($actions)
	{
		foreach ($actions as $action)
		{
			$controllerAction = $action->Controller_Action;

			$controllerAction->set (self::dispatch (
				$controllerAction->controller,
				$controllerAction->action
			));
		}
		
		return $actions;
	}
}