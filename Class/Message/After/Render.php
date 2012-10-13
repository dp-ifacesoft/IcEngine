<?php
/**
 * @desc Событие перед началом рендера из фронт контроллера.
 * @author Юрий
 * @package IcEngine
 */
class Message_After_Render extends Message_Abstract
{
	
	public static function push ($view, array $params = array ())
	{
		Message_Queue::push (
			'After_Render',
			array_merge (
				$params,
				array ('view'	=> $view)
			)
		);
	}
	
}