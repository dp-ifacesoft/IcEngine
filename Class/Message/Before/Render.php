<?php
/**
 * @desc Событие перед началом рендера из фронт контроллера.
 * @author Юрий
 * @package IcEngine
 */
class Message_Before_Render extends Message_Abstract
{
	
	public static function push ($view, array $params = array ())
	{
		Message_Queue::push (
			'Before_Render',
			array_merge (
				$params,
				array ('view'	=> $view)
			)
		);
	}
	
	/**
	 * @return View_Render_Abstract
	 */
	public function view ()
	{
		return $this->view;
	}
	
}