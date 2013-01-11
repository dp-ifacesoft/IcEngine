<?php

/**
 * Модель гостя (незарегистрированного посетителя сайта).
 *
 * @author goorus, morph
 * @Service("userGuest")
 */
class User_Guest extends User_Cli
{
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $config = array(
		/**
		 * @desc Конфиг пользователя
		 * @var array
		 */
		'fields'	=> array(
			'id'		=> 0,
			'active'	=> 1,
			'login'		=> '',
			'name'		=> '',
			'email'		=> '',
			'password'	=> ''
		)
	);

    /**
	 * @inheritdoc
	 */
	public function init($sessionId = null)
	{
		$instance = $this->getInstance();
        $resourceManager = $this->getService('resourceManager');
		$resourceManager->set('Model', $instance->resourceKey(), $instance);
	}
}