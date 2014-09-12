<?php

/**
 * Авторизация через логинзу
 *
 * @author neon, goorus, morph
 */
class Authorization_Loginza extends Authorization_Abstract
{
	/**
	 * @inheritdoc
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize($data)
	{
		$authorizationLoginzaToken = $this->getService(
			'authorizationLoginzaToken'
		);
		$token = $authorizationLoginzaToken->tokenData();
		$userLoginza = $this->getService('userLoginza');
		$loginza = $userLoginza->byToken($token, true, true);
		$user = $loginza ? $loginza->User : null;
		if (!$user) {
			$user = $this->autoregister($token);
		}
		return $user instanceof User ? $user->authorize() : $user;
	}

	/**
	 * Авторегистрация
	 *
	 * @param Authorization_Loginza_Token $token
	 * @return User|int
	 */
	public function autoregister($token)
	{
		if (!$token->email) {
			return;
		}
        $identity = (string) $token->identity;
        $email = (string) $token->email;
		$data = $token->data('data');
		//$userService = $this->getService('user');
		$modelManager = $this->getService('modelManager');
		$queryBuilder = $this->getService('query');
        $userLoginza = $modelManager->byQuery(
            'User_Loginza',
            $queryBuilder->where('identity', $identity)
        );
        if ($userLoginza) {
            $user = $userLoginza->User;
        }
        if (!$user) {
            $userQuerySelect = $queryBuilder->select('*')
                ->from('User')
                ->where('Login', $identity);
            $user = $modelManager->byQuery($userQuerySelect);
        }
        /*if (!$user) {
            $user = $userService->create(array(
                'firstName'		=> $email,
                'login'         => $identity,
                'email'         => $email,
                'password'      => md5(time()),
                'active'        => 1
            ));
        }*/
		if ($userLoginza && $user) {
			$userLoginza->update(array(
				'User__id'	=> $user->key()
			));
		} else {
			$helperDate = $this->getService('helperDate');
			$userLoginza = new User_Loginza(array(
				'User__id'	=> $user ? $user->key() : 0,
				'identity'	=> $identity,
				'email'		=> $email,
				'provider'	=> (string) $token->provider,
				'result'	=> json_encode($data),
				'createdAt'	=> $helperDate->toUnix()
			));
			$userLoginza->save();
		}
		return $user ? $user : 0;
	}

	/**
	 * @inheritdoc
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered($login)
	{
		return false;
	}

	/**
	 * @inheritdoc
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin($login)
	{
		return false;
	}

	/**
	 * @inheritdoc
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser($data)
	{
		$authorizationLoginzaToken = $this->getService(
			'authorizationLoginzaToken'
		);
		$token = $authorizationLoginzaToken->tokenData($data);
		$userLoginza = $this->getService('userLoginza');
		$loginza = $userLoginza->byToken($token);
		return $loginza ? $loginza->User : null;
	}
}