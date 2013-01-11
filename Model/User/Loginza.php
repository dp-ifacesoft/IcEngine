<?php

/**
 * Данные по OpenID пользователя
 *
 * @author goorus, morph
 * @Service("userLoginza")
 */
class User_Loginza extends Model
{
	/**
	 * Находит данные пользователя по полученному ключу
	 *
     * @param Authorization_Loginza_Token $token.
	 * @param boolean $email_search Искать по email. Необходимо, чтобы
	 * $token содержал не пустое поле email.
	 * @param boolean $user_search Создать модель, если таковой не существует
	 * (будет произведен поиск по полю email в таблице User). Необходимо, чтобы
	 * $token содержал не пустое поле email.
	 * @return User_Loginza
	 */
	public function byToken($token, $emailSearch = true,
        $userSearch = true)
	{
		if (!$token->identity) {
			return;
		}
		$modelManager = $this->getService('modelManager');
		$query = $this->getService('query');
		$loginza = $modelManager->byQuery(
			__CLASS__, $query->where('identity', $token->identity)
		);
        $helperDate = $this->getService('helperDate');
		if (!$loginza && $emailSearch && $token->email) {
			$otherLoginza = $modelManager->byQuery(
				__CLASS__, $query->where('email', $token->email)
			);
			if ($otherLoginza) {
				$loginza = new self(array(
					'User__id'	=> $otherLoginza->User__id,
					'identity'	=> $token->identity,
					'email'		=> $token->email,
					'provider'	=> $token->provider,
					'result'    => $token->result,
					'createdAt'	=> $helperDate->toUnix()
				));
				return $loginza->save();
			}
		}
		if (!$loginza && $userSearch && $token->email) {
			$user = $modelManager->byQuery(
				'User', $query->where('email', $token->email)
			);
			if ($user) {
				$loginza = new self(array(
					'User__id'		=> $user->id,
					'identity'		=> $token->identity,
					'email'			=> $token->email,
					'provider'		=> $token->provider,
					'result'		=> $token->result,
					'createdAt'		=> $helperDate->toUnix()
				));
				return $loginza->save();
			}
		}
		if (!$loginza) {
			$loginza = new self(array(
				'User__id'		=> 0,
				'identity'		=> $token->identity,
				'email'			=> $token->email,
				'provider'		=> $token->provider,
				'result'		=> $token->result,
				'createdAt'		=> $helperDate->toUnix()
			));
			$loginza->save();
		}
		return $loginza;
	}
}