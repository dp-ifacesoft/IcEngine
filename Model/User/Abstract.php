<?php

/**
 * Абстрактная модель пользователя.
 *
 * @author goorus, morph, neon
 * @Service("user")
 */
class User_Abstract extends Model
{
	/**
	 * @inheritdoc
	 */
	protected static $config = array(
		// колбэк после авторизации
		'login_callback'	=> null,
		// функция, вызываемая при логауте.
		'logout_callback'	=> null
	);

	/**
	 * Текущий пользователь.
	 *
     * @var User
	 */
	protected $current;
    
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
        $session = $this->getService('session')->getCurrent();
		$session->updateSession($this->key());
        $userService = $this->getService('user');
        $userService->setCurrent($this);
        $this->update(array(
            'phpSessionId'  => $session->key()
        ));
        $authorizationLog = $this->getService("authorizationLog");
        $authorizationLog->log();
        $afterCallbackManager = $this->getService('afterCallbackManager');
        $afterCallbackManager->apply();
		return $this;
	}

	/**
	 * Проверяет, авторизован ли пользователь.
	 *
     * @return boolean True, если пользователь авторизован, иначе false.
	 */
	public function authorized()
	{
        $userService = $this->getService('user');
		return $userService->current->id > 0 ? true : false;
	}

	/**
	 * Проверяет, имеет ли пользователь доступ.
	 *
     * @param string|integer $name
	 * 		Алиас или id ресурса
	 * @return boolean
	 */
	public function can($name)
	{
        $modelManager = $this->getService('modelManager');
		if (is_numeric($name)) {
			$resource = $modelManager->get('Acl_Resource', $name);
		} else {
			$resource = $modelManager->byOptions(
				'Acl_Resource',
                array(
                    'name'  => '::Name',
                    'value' => $name
                )
			);
		}
		if (!$resource) {
			return false;
		}
		return $resource->userCan($this);
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
        //иначе пароля не будет в RSAW2
        if (strlen($data['password']) < 4) {
            return;
        }
        if (!isset($data['login']) && !isset($data['email'])) {
            return false;
        }
        if (!isset($data['login'])) {
            $data['login'] = $data['email'];
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
	 * Возвращает id текущего пользователя.
	 *
     * @return integer id текущего пользователя.
	 */
	public function id()
	{
		if (!$this->current || !$this->current->key()) {
			return 0;
		}
		return $this->current->key();
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
     * @param Acl_Role|string $role Роль или название роли
	 * @param $_
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
     * @param string $session_id Идентификатор сессии.
	 * @return User Пользователь.
	 */
	public function init($sessionId = null)
	{
        if ($this->current) {
            return;
        }
        $request = $this->getService('request');
		$sessionId = $sessionId ?:$request->sessionId();
        $session = $this->getService('session');
        $userSession = $session->byPhpSessionId($sessionId ?: 'unknown');
        $session->setCurrent($userSession);
		$this->current = $session->getCurrent()->User;
		$session->getCurrent()->updateSession();
		return $this->current;
	}

	/**
	 * Логаут. Удаление сессии.
	 */
	public function logout()
	{
		$session = $this->getService('session');
		$session->getCurrent()->delete();
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