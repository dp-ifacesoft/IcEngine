<?php

/**
 * Сессия пользователя
 *
 * @author goorus, morph, neon
 */
abstract class User_Session_Abstract extends Model
{
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
            $sessionData = array(
    			'id'			=> $sessionId,
    			'User__id'		=> $this->defaultUserId,
    			'phpSessionId'	=> $sessionId,
    			'startTime'	    => $date->toUnix(),
    			'lastActive'	=> $date->toUnix(),
                'url'           => $request->uri(),
    			'remoteIp'		=> $request->ip(),
				'eraHourNum'	=> $date->eraHourNum(),
    			'userAgent'	    => substr(getenv('HTTP_USER_AGENT'), 0, 64)
    		);
    		$session = $modelManager->create('User_Session', array_merge(
                $sessionData, $this->getParams()
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
        if (!$this->current) {
            $sessionId = $this->getService('request')->sessionId();
            $userSession = $this->byPhpSessionId($sessionId);
            $this->setCurrent($userSession);
        }
	    return $this->current;
	}

	/**
	 * Возвращает ПК пользователя по умолчанию.
	 *
     * @return integer
	 */
	public function getDefaultUserId()
	{
		return $this->defaultUserId;
	}

    /**
     * @return array()
     */
    abstract public function getParams();

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
        $updateData = array(
            'lastActive'	=> $now,
            'eraHourNum'	=> $date->eraHourNum(),
            'url'           => $this->getService('request')->uri()
        );
		if ($newUserId) {
			$updateData['User__id'] = $newUserId;
        }
        $data = array_merge($updateData, $this->getParams());
        $this->update($data);
		return $this;
	}
}