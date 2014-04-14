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
		$locator = IcEngine::serviceLocator();
		$cryptManager = $locator->getService('cryptManager');
        $password = $this->params['value'];
        $queryBuilder = $locator->getService('query');
        $passwordMd5 = md5($password);
        $passwordQueryWhere = $queryBuilder
            ->where('password', $passwordMd5);
        $configManager = $locator->getService('configManager');
        $userConfig = $configManager->get('User');
        if ($userConfig->cryptManager) {
            $crypter = $cryptManager->get($userConfig->cryptManager);
            $passwordCrypted = $crypter->encode($password);
            $passwordQueryWhere->orWhere('password', $passwordCrypted);
        }
        $this->query
            ->where($passwordQueryWhere)
            ->where('password != ""');
	}
}