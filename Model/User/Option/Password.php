<?php

/**
 * Опшен для получения юзера по паролю
 *
 * @author neon
 */
class User_Option_Password extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
        $userConfig = Config_Manager::get('User');
        $crypt = Crypt_Manager::get($userConfig['password']['crypt']);
        $password = $crypt->encode($this->params['value']);
        $this->query
            ->where('password', $password);
	}
}