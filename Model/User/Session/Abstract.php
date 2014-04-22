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
    protected static $current;

	/**
	 * id пользователя по умолчаиню
	 *
     * @var integer
	 */
	protected static $defaultUserId = 0;

    /**
     * Данные для обновления, используется, чтобы не было лишних запросов на
     * обновление
     *
     * @var array
     */
    protected $updateData = array();

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
		$session = User_Session::getModel($sessionId);
        $request = $this->getService('request');
		if (!$session && $autocreate) {
            $sessionData = array(
    			'id'			=> $sessionId,
    			'User__id'		=> self::$defaultUserId,
    			'phpSessionId'	=> $sessionId,
    			'lastActive'	=> time(),
                'url'           => $request->uri(),
    			'remoteIp'		=> $request->ip(),
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
     * @param string $sessionId
	 * @return User_Session
	 */
	public function getCurrent($sessionId = null)
	{
        if (!self::$current) {
            $sessionId = $sessionId ?:
                $this->getService('request')->sessionId();
            $userSession = $this->byPhpSessionId($sessionId);
            $this->setCurrent($userSession);
        }
	    return self::$current;
	}

	/**
	 * Возвращает ПК пользователя по умолчанию.
	 *
     * @return integer
	 */
	public function getDefaultUserId()
	{
		return self::$defaultUserId;
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
	    self::$current = $session;
	}

	/**
	 * Устанавливает id пользователя по умолчанию
	 *
     * @param integer $value ПК пользователя
	 */
	public function setDefaultUserId($id)
	{
		self::$defaultUserId = $id;
	}

    /**
     * Установить данные для обновления
     *
     * @param array $data
     */
    public function setUpdateData($data)
    {
        $this->updateData = array_merge($this->updateData, $data);
    }

	/**
     * Обновляет данные сессии
     *
	 * @param integer $new_user_id [optional] Изменить пользователя.
	 * @return User_Session
	 */
	public function updateSession($newUserId = null)
	{
		$now = time();
        $url = $this->getService('request')->uri();
        $updateData = array();
        if ($this->url != $url) {
            $updateData['url'] = $url;
        }
        if ($now - $this->lastActive > 300) {
            $updateData['lastActive'] = $now;
        }
        if (!isset($this->User__id)) {
            $this->User__id = self::$defaultUserId;
        }
		if ($newUserId && $newUserId != $this->User__id) {
			$updateData['User__id'] = (int) $newUserId;
        }
        $data = array_merge($updateData, $this->updateData);
        if ($data) {
            $this->update($data);
        }
		return $this;
	}
}