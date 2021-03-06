<?php

/**
 * Абстрактная модель пользователя.
 *
 * @author goorus, morph, neon
 */
class User_Abstract extends Model
{
    /**
     * Текущий пользователь.
     *
     * @var User
     */
    protected $current;
    
     /**
     * Текущая сессия.
     * 
     * @var Session
     */
    protected $currentSession;

    /**
     * Доступные пользователю роли
     *
     * @var array
     */
    protected $roleExists = array();

	/**
	 * Авторизоваться этим пользователем.
	 *
     * @return User
	 */
	public function authorize()
	{
        $userService = $this->getService('user');
        $userService->setCurrent($this);
        $session = $this->getService('userSession')->getCurrent();
        $roles = $this->getService('helperAdmin')->getUserRoles();
        $session->updateSession($this->key(), [
            'userRole'  => json_encode($roles)
        ]);
        $this->update(array('phpSessionId' => $session->key()));
        $authorizationLog = $this->getService('authorizationLog');
        $authorizationLog->log();
//        $afterCallbackManager = $this->getService('afterCallbackManager');
//        $afterCallbackManager->apply();
		return $this;
	}

	/**
	 * Проверяет, авторизован ли пользователь.
         * В действительности это лишь проверка на отличие от гостя.
	 *
     * @return boolean True, если пользователь авторизован, иначе false.
	 */
	public function authorized()
	{
            return $this->key() > 0;
	}

	/**
	 * Создание пользователя.
	 *
     * @param array|Objective $data Данные пользователя.
	 * $param ['email'] Емейл
	 * $param ['password'] Пароль
	 * $param ['active'] = 0 Активен
	 * $param ['ip'] IP пользователя при регистрации
	 * @return Model|false
	 */
	public function create($data)
	{
		if (is_object($data)) {
			$data = $data->__toArray();
		}
		if (!isset($data['ip'])) {
			$data['ip'] = $this->getService('request')->ip();
		}
        if (!isset($data['login']) && !isset($data['email']) && !isset($data['phone'])) {
            return false;
        }
        if (!isset($data['login']) && isset($data['email'])) {
            $data['login'] = $data['email'];
        }
        if (!isset($data['login']) && isset($data['phone'])) {
             $data['login'] = $data['phone'];
        }
        $cryptManager = $this->getService('cryptManager');
        $configManager = $this->getService('configManager');
        $userConfig = $configManager->get('User');
        $crypt = $cryptManager->get($userConfig->cryptManager);
        $passwordCrypted = $crypt->encode($data['password']);
        $data['password'] = $passwordCrypted;
		$user = new User($data);
		return $user->save();
	}

	/**
	 * Возвращает модель текущего пользователя.
	 * Если пользователь не авторизован, будет возвращает экземпляр User_Guest.
	 *
     * @return User Текущий пользователь.
	 */
	public function getCurrent()
	{
        return $this->current;
	}

    /**
     * Получить ид текущей сессии пользователя
     * @return string
     */
    public function getSessionId()
    {
        return $this->phpSessionId;
    }

    /**
     * Получить роли пользователя
     * @return mixed ассоциативный массив с ролями пользователя
     */
    public function getRoles() {
        $session = $this->getService('userSession')->getCurrent();
        return json_decode($session->userRole);
    }

	/**
	 * Возвращает id текущего пользователя.
	 *
     * @return integer id текущего пользователя.
	 */
	public function id()
	{
            $currentUser = $this->getService('user')->getCurrent();
		if (!$currentUser) {
			return 0;
		}
		return $currentUser->key();
	}

	/**
	 * Проверяет, имеет ли пользователь роль админа.
	 *
     * @return boolean true, если имеет, иначе false.
	 */
	public function isAdmin()
	{
		return $this->hasRole('admin');
	}

    /**
     * Проверяет является ли пользователь консольным пользователем
     *
     * @return boolean
     */
    public function isCli()
    {
        return $this->key() < 0;
    }

	/**
	 * Проверяет, является ли этот пользователем текущим.
	 * Т.е. авторизован от имени этого пользователя.
	 *
     * @return boolean
	 */
	public function isCurrent()
	{
		return $this->authorized() && ($this->id() == $this->key());
	}

    /**
     * Проверяет, имеет ли пользователь хотя бы одну из указанных ролей.
     *
     * @param Acl_Role|string $role Роль или название р оли
     * @return boolean Имеет ли пользователь роль.
     */
	public function hasRole($role)
	{
        $roleNames = array();
        $roles = array();
        $args = $role;
        if (is_array($role) && count($role) == 1 && is_array($role[0])) {
            $args = $role[0];
        }
        if (!is_array($args)) {
            $args = array($args);
        }
		foreach ((array) $args as $role) {
			if (!$role) {
                continue;
            }
            if (!is_object($role)) {
                $roleNames[] = $role;
            } else {
                $roles[] = $role->asRow();
            }
        }
        if ($roleNames) {
            $collectionManager = $this->getService('collectionManager');
            $roleCollection = $collectionManager->create('Acl_Role')
                ->addOptions(array(
                    'name'  => '::Name',
                    'value' => $roleNames
                ))->raw();
            if ($roleCollection) {
                foreach ($roleCollection as $role) {
                    $roles[] = $role;
                }
            }
        }
        $helperArray = $this->getService('helperArray');
        $existsRoleNames = $helperArray->column($roles, 'name');
        if (!$existsRoleNames) {
            return false;
        }
        foreach ($existsRoleNames as $roleName) {
            if (isset($this->roleExists[$roleName])) {
                return true;
            }
        }
        $roleIds = $helperArray->column($roles, 'id');
        $queryBuilder = $this->getService('query');
        $query = $queryBuilder
            ->select('fromRowId')
            ->from('Link')
            ->where('fromTable', 'Acl_Role')
            ->where('toTable', 'User')
            ->where('fromRowId', $roleIds)
            ->where('toRowId', $this->key());
        $dds = $this->getService('dds');
        $existsRoleIds = $dds->execute($query)->getResult()->asColumn();
        if (!$existsRoleIds) {
            return false;
        }
        $roleExists = false;
        foreach ($existsRoleIds as $roleId) {
            $roleFiltered = $helperArray->filter($roles, array(
                'id'    => $roleId
            ));
            if (!$roleFiltered) {
                continue;
            }
            $roleExists = true;
            $this->roleExists[$roleFiltered[0]['name']] = true;
        }
        return $roleExists;
	}

    /**
     * Инициализация пользователя.
     * Создание моделей сессии и пользователя.
     *
     * @param null $sessionId
     * @internal param string $session_id Идентификатор сессии.
     * @return User Пользователь.
     */
	public function init($sessionId = null)
	{
        $request = $this->getService('request');
		$sessionId = $sessionId ?: $request->sessionId();
        $session = $this->getService('userSession');
        $userSession = $session->getCurrent($sessionId);
		$this->current = $userSession->User;
		$session->getCurrent()->updateSession($this->current->key());
		return $this->current;
	}

	/**
	 * Логаут. Удаление сессии.
	 */
	public function logout()
	{
		$session = $this->getService('userSession');
		$session->getCurrent()->delete();
	}
    
    /**
     * @inheritdoc
     */
    public function table()
    {
        return 'User';
    }

    /**
     *  @inheritodc
     */
	public function title()
	{
		return $this->login . ' ' . $this->name;
	}

    /**
     * Изменить текущего пользователя
     *
     * @param type $user
     */
	public function setCurrent($user)
	{
		$this->current = $user;
	}
}