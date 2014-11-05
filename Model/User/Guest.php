<?php

/**
 * Модель гостя (незарегистрированного посетителя сайта).
 *
 * @author goorus, morph
 * @Service("userGuest", disableConstruct=true)
 */
class User_Guest extends User_Cli
{
	/**
	 * @inheritdoc
	 */
	protected static $config = array(
		'fields'	=> array(
			'id'		=> 0,
			'active'	=> 1,
			'login'		=> '',
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