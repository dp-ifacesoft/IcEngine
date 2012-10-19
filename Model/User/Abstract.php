<?php
/**
 *
 * @desc Абстрактная модель пользователя.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class User_Abstract extends Model
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		// колбэк после авторизации
		'login_callback'	=> null,
		// функция, вызываемая при логауте.
		'logout_callback'	=> null
	);

	/**
	 * @desc Текущий пользователь.
	 * @var User
	 */
	protected static $_current	= false;

	/**
	 * @desc Авторизоваться этим пользователем.
	 * @return User
	 */
	public function authorize ()
	{
		User_Session::getCurrent ()->updateSession ($this->id);
		self::$_current = $this;

		$config = $this->config ();
		if ($config ['login_callback'])
		{
			list ($class, $method) = explode (
				'::',
				$config ['login_callback']
			);
			call_user_func (
				array ($class, $method),
				$this
			);
		}

		return $this;
	}

	/**
	 * @desc Проверяет, авторизован ли пользователь.
	 * @return boolean True, если пользователь авторизован, иначе false.
	 */
	public static function authorized ()
	{
		return (bool) self::id ();
	}

	/**
	 * @desc Проверяет, имеет ли пользователь доступ.
	 * @param string|integer $alias
	 * 		Алиас или id ресурса
	 * @return boolean
	 */
	public function can ($alias)
	{
		if (is_numeric ($alias))
		{
			$resource = Model_Manager::get ('Acl_Resource', $alias);
		}
		else
		{
			$resource = Model_Manager::byQuery (
				'Acl_Resource',
				Query::instance ()
					->where ('alias', $alias)
			);
		}

		if (!$resource)
		{
			return false;
		}

		return $resource->userCan ($this);
	}

	/**
	 * @desc Создание пользователя.
	 * @param array|Objective $data Данные пользователя.
	 * $param ['email'] Емейл
	 * $param ['password'] Пароль
	 * $param ['active'] = 0 Активен
	 * $param ['ip'] IP пользователя при регистрации
	 * @return User
	 */
	public static function create ($data)
	{
		if (is_object ($data))
		{
			$data = $data->__toArray ();
		}

		if (!isset ($data ['ip']))
		{
			$data ['ip'] = Request::ip ();
		}

		$user = new User ($data);

		return $user->save ();
	}

	/**
	 * @desc Генерация пароля заданной длинны.
	 * @param integer $length
	 * @return string
	 */
	public static function genPassword ($length = 8)
	{
		$result = substr (md5 (time), 0, $length);
	}

	/**
	 * @desc Возвращает модель текущего пользователя.
	 * Если пользователь не авторизован, будет возвращает экземпляр User_Guest.
	 * @return User Текущий пользователь.
	 */
	public static function getCurrent ()
	{
		return self::$_current;
	}

	/**
	 * @desc Возвращает id текущего пользователя.
	 * @return integer id текущего пользователя.
	 */
	public static function id ()
	{
		if (!self::$_current || !self::$_current->id)
		{
			return 0;
		}

		return self::$_current->id;
	}

	/**
	 * @desc Проверяет, имеет ли пользователь роль админа.
	 * @return boolean true, если имеет, иначе false.
	 */
	public function isAdmin ()
	{
		return $this->hasRole (Acl_Role::byName ('admin'));
	}

	/**
	 * @desc Проверяет, является ли этот пользователем текущим.
	 * Т.е. авторизован от имени этого пользователя.
	 * @return boolean
	 */
	public function isCurrent ()
	{
		return self::authorized () && (self::id () == $this->key ());
	}

	/**
	 * Проверяет есть ли у юзера доступ к содержимому, админских блоков и т д
	 * @return boolean
	 */
	public function accessToAdmin()
	{
		if (self::id() > 0) {
			$user = self::getCurrent();
			if ($user->hasRole('editor') ||
				$user->hasRole('admin')) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @desc Проверяет, имеет ли пользователь хотя бы одну из указанных ролей.
	 * @param Acl_Role|string $role Роль или название роли
	 * @param $_
	 * @return boolean Имеет ли пользователь роль.
	 */
	public function hasRole ($role)
	{
		foreach (func_get_args () as $role)
		{
			if ($role)
			{
				if (!is_object ($role))
				{
					$role = Acl_Role::byName ($role);
				}

				if ($role && Helper_Link::wereLinked ($this, $role))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @desc Проверяет имеет ли пользователь роль с указаным типом
	 * @param integer $type_id
	 */
	public function hasRoleWithType ($type_id)
	{
		$collection = Helper_Link::linkedItems (
			$this,
			'Acl_Role'
		);
		$collection
			->where ('Acl_Role_Type__id=?', $type_id);
		return !$collection->isEmpty ();
	}

	/**
	 * @desc Инициализация пользователя.
	 * Создание моделей сессии и пользователя.
	 * @param string $session_id Идентификатор сессии.
	 * @return User Пользователь.
	 */
	public static function init ($session_id = null)
	{
		$session_id = $session_id ? $session_id : Request::sessionId ();
		User_Session::setCurrent (
			User_Session::byPhpSessionId (
				$session_id ? $session_id : 'unknown')
		);

		self::$_current = User_Session::getCurrent ()->User;
		User_Session::getCurrent ()->updateSession ();

		return self::$_current;
	}

	/**
	 * @desc Логаут. Удаление сессии.
	 */
	public function logout ()
	{
		$config = $this->config ();
		if ($config ['logout_callback'])
		{
			list ($class, $method) = explode (
				'::',
				$config ['logout_callback']
			);

			call_user_func (
				array ($class, $method),
				$this
			);
		}
		User_Session::getCurrent ()->delete ();
	}

	/**
	 * @return Acl_Role
	 */
	public function personalRole ()
	{
		$role_name = 'User' . $this->id . 'Personal';

		$role = Acl_Role::byTypeNName (
			Acl_Role_Type_Personal::ID,
			$role_name
		);

		if (!$role)
		{
			$role = new Acl_Role (array (
				'name'				=> $role_name,
				'Acl_Role_Type__id'	=> Acl_Role_Type_Personal::ID
			));
			$role->save ();
		}

		return $role;
	}

	/**
	 * @desc Получение роли указанного типа, которую имеет пользователь.
	 * Если пользователь не имеет роли указанного типа и $autocreate,
	 * такая роль будет создана и присвоена пользователю.
	 * @param integer $role_type_id Тип роли.
	 * @param boolean $autocreate Создавать ли роль в случае отсутсвтия.
	 * @return Acl_Role Роль.
	 */
	public function role ($role_type_id, $autocreate = false)
	{
		$role = Helper_Link::linkedItems (
			$this,
			'Acl_Role'
		);

		if ($role)
		{
			$role
				->addOptions (array (
					'name'			=> 'Role_Type',
					'role_type_id'	=> $role_type_id
				));
			$role = $role->first ();
		}

		if (!$role && $autocreate)
		{
			$role = new Acl_Role (array (
				'name'				=> 'u' . $this->id . 'rt' . $role_type_id,
				'Acl_Role_Type__id'	=> $role_type_id
			));
			$role->save ();
			Helper_Link::link ($role, $this);
		}

		return $role;
	}

	public function title ()
	{
		return $this->login . ' ' . $this->name;
	}

	public static function setCurrent($user)
	{
		self::$_current = $user;
	}

}