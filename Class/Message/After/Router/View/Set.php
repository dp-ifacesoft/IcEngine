<?php
/**
 * @desc Событие после определения роутера и установки соответсвующего ему
 * рендера.
 * Основное назначение - подмена шаблона рендера.
 * @author Юрий
 * @package IcEngine
 *
 */
class Message_After_Router_View_Set extends Message_Abstract
{
	
	public static function push (Route $route, View_Render_Abstract $view,
		array $params = array ())
	{
		IcEngine::$messageQueue->push (
			'After_Router_View_Set',
			array_merge (
				$params,
				array (
					'view'		=> $view,
					'route'		=> $route
				)
			)
		);
	}
	
	/**
	 * @return Router
	 */
	public function route ()
	{
		return $this->route;
	}
	
	/**
	 * @return View_Render_Abstract
	 */
	public function view ()
	{
		return $this->view;
	}
	
}