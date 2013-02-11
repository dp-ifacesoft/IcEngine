<?php

/**
 * Сессия пользователя
 *
 * @author goorus, morph
 * @Service("session")
 */
class User_Session extends Model
{
    /**
     * Первый доступ
     *
     * @var boolean
     */
    protected $firstAttend;

    /**
     * Сессия текущего пользователя.
     *
     * @var User_Session
     */
    protected $current;

	/**
	 * id пользователя по умолчаиню
	 *
     * @var integer
	 */
	protected $defaultUserId = 0;

	/**
	 * Получить сессию пользователя по phpSessionId
     *
	 * @param string $sessionId
     * @param boolean $autocreate
	 * @return User_Session
	 */
	public function byPhpSessionId($sessionId, $autocreate = true)
	{
        $modelManager = $this->getService('modelManager');
		$session = $modelManager->byKey('User_Session', $sessionId);
        $date = $this->getService('helperDate');
        $request = $this->getService('request');
		if (!$session && $autocreate) {
    		$session = $modelManager->create('User_Session', array(
    			'id'			=> $sessionId,
    			'User__id'		=> $this->defaultUserId,
    			'phpSessionId'	=> $sessionId,
    			'startTime'	    => $date->toUnix(),
    			'lastActive'	=> $date->toUnix(),
                'url'           => $request->uri(),
    			'remoteIp'		=> $request->ip(),
				'eraHourNum'	=> $date->eraHourNum(),
    			'userAgent'	    => substr(getenv('HTTP_USER_AGENT'), 0, 100)
    		));
    		$session->save(true);
		}
		return $session;
	}

	/**
     * Получить текущую сессию пользователя
     *
	 * @return User_Session
	 */
	public function getCurrent ()
	{
        if (!$this->firstAttend) {
            if (!$this->current) {
                $sessionId = $this->getService('request')->sessionId();
                $userSession = $this->byPhpSessionId($sessionId);
                $this->setCurrent($userSession);
            }
            $this->current->updateSession();
            $this->firstAttend = true;
        }
	    return $this->current;
	}

	/**
	 * Возвращает ПК пользователя по умолчанию.
	 *
     * @return integer
	 */
	public function getDefaultUserId ()
	{
		return $this->defaultUserId;
	}

	/**
	 * Изменить текущую сессию пользователя
     *
	 * @param User_Session $session
	 */
	public function setCurrent(User_Session $session)
	{
	    $this->current = $session;
	}

	/**
	 * Устанавливает id пользователя по умолчанию
	 *
     * @param integer $value ПК пользователя
	 */
	public function setDefaultUserId($id)
	{
		$this->defaultUserId = $id;
	}

	/**
     * Обновляет данные сессии
     *
	 * @param integer $new_user_id [optional] Изменить пользователя.
	 * @return User_Session
	 */
	public function updateSession($newUserId = null)
	{
        $date = $this->getService('helperDate');
		$now = $date->toUnix();
        $updateData = array();
		if ($newUserId) {
			$updateData = array(
				'User__id'		=> $newUserId,
				'lastActive'	=> $now,
				'eraHourNum'	=> $date->eraHourNum()
			);
		} else {
			// Обновляем сессию не чаще, чем раз в 10 минут.
			// strlen ('YYYY-MM-DD HH:I_:__') =
			if (strncmp($now, $this->lastActive, 15) == 0) {
				return $this;
			}
			$updateData = array(
				'lastActive'	=> $now,
				'eraHourNum'	=> $date->eraHourNum()
			);
		}
        $updateData['url'] = $this->getService('request')->uri();
        if ($updateData) {
            $this->update($updateData);
        }
		return $this;
	}
}