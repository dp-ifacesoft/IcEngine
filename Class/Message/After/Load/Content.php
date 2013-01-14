<?php
/**
 * Событие после загрузки контента контроллером.
 * @author Юрий
 * @package IcEngine
 *
 */
class Message_After_Load_Content extends Message_Abstract
{
	
	/**
	 * @return Model
	 */
	public function model ()
	{
		return $this->model;
	}
	
	/**
	 * @return mixed
	 */
	public function key ()
	{
		return $this->model ()->key ();
	}
	
	/**
	 * 
	 * @param Model $model
	 * @param array $params
	 * @return Message_After_Load_Content
	 */
	public static function push (Model $model, array $params = array ())
	{
		return IcEngine::$messageQueue->push (
			'After_Load_Content',
			array_merge (
				$params,
				array (
					'model'	=> $model
				)
			)
		);
	}
	
	/**
	 * @return string
	 */
	public function table ()
	{
		return $this->model ()->table ();
	}
	
}