<?php

/**
 * Данные по OpenID пользователя
 *
 * @author neon, goorus, morph
 * @Service("userLoginza")
 */
class User_Loginza extends Model
{
	/**
	 * Находит данные пользователя по полученному ключу
	 *
     * @param Authorization_Loginza_Token $token.
	 * @param boolean $user_search Создать модель, если таковой не существует
	 * (будет произведен поиск по полю email в таблице User). Необходимо, чтобы
	 * $token содержал не пустое поле email.
	 * @return User_Loginza
	 */
	public function byToken($token, $userSearch = true)
	{
		if (!$token->identity) {
			return;
		}
		$modelManager = $this->getService('modelManager');
		$queryBuilder = $this->getService('query');
		$loginza = $modelManager->byQuery(
			__CLASS__, $queryBuilder->where('identity', $token->identity)
		);
        $helperDate = $this->getService('helperDate');
		if (!$loginza && $token->identity) {
            if ($userSearch) {
                $user = $modelManager->byQuery(
                    'User',
                    $queryBuilder
                        ->where('email', $token->email)
                        ->where('email != ""')
                );
            }
			$loginza = new self(array(
				'User__id'		=> $user ? $user->key() : 0,
				'identity'		=> $token->identity,
				'email'			=> $token->email,
				'provider'		=> $token->provider,
				'result'		=> urlencode($token->result),
				'createdAt'		=> $helperDate->toUnix()
			));
			$loginza->save();
		}
		return $loginza;
	}
}