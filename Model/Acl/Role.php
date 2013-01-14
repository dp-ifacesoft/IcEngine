<?php
/**
 *
 * @desc Роли доступа
 * @author Илья Колесников, Юрий Шведов
 * @package IcEngine
 *
 *
 */
class Acl_Role extends Model
{

	/**
	 * @desc Название роли гостя.
	 * @var string
	 */
	const GUEST_ROLE_NAME	= 'guest';

	/**
	 * @desc Предоставление роли права на ресурс или ресурсы.
	 * @param Acl_Resource $resource
	 * @return Acl_Role
	 */
	public function attachResource (Acl_Resource $resource)
	{
	    foreach (func_get_args () as $res)
	    {
	        Helper_Link::link ($this, $res);
	    }
	    return $this;
	}

	/**
	 * @desc Добавление роли пользователю.
	 * @param User $user
	 * @return Acl_Role
	 */
	public function attachUser (User $user)
	{
	    Helper_Link::link ($this, $user);
		return $this;
	}

	/**
	 *
	 * @param string $name
	 * @return Acl_Role|null
	 */
	public static function byName ($name)
	{
	    return Model_Manager::byQuery (
		    'Acl_Role',
		    Query::instance ()
		   		->where ('name', $name)
		);
	}

	/**
	 *
	 * @param integer $type_id
	 * @param string $name
	 * @return Acl_Role|null
	 */
	public static function byTypeNName ($type_id, $name)
	{
		return Model_Manager::byQuery (
		    'Acl_Role',
		    Query::instance ()
			    ->where ('Acl_Role_Type__id', $type_id)
			    ->where ('name', $name)
		);
	}

	/**
	 * @desc Делает ресурс недоступным для роли
	 * @param Acl_Resource $resource
	 * @return Acl_Role
	 */
	public function deattachResource (Acl_Resource $resource)
	{
		return $this->unjoin ($resource);
	}

	/**
	 * @desc Забирает роль у пользователя
	 * @param User $user
	 * @return Acl_Role
	 */
	public function deattachUser (User $user)
	{
		return $this->unjoin ($user);
	}

	/**
	 * @desc Дает роли доступ к ресурсу.
	 * @param array $_ Ресурс
	 */
	public function grant ($_)
	{
		if (!is_array ($_))
		{
		    $_ = func_get_args ();
		}

		$resource = Acl_Resource::byNameAuto ($_);

		$this->attachResource ($resource);
	}

	/**
	 * @desc Имеет ли роль доступ к ресурсу
	 * @param array $_ Ресурс
	 */
	public function hasAccess ($_)
	{
		if (!is_array ($_))
		{
			$_ = func_get_args ();
		}

		$resource = Acl_Resource::byNameCheck ($_);

		return $resource ? $this->resourceAttached ($resource) : false;
	}

	/**
	 * @desc Имеет ли роль доступ к ресурсу.
	 * @param Acl_Resource $resource_id
	 * @return boolean
	 */
	public function resourceAttached (Acl_Resource $resource)
	{
		return Helper_Link::wereLinked ($this, $resource);
	}

	/**
	 * @desc Отнимает право на ресурс.
	 * @param array|string $_ Ресурс
	 */
	public function revoke ($_)
	{
		if (is_array ($_))
		{
			$resource = Acl_Resource::byNameCheck ($_);
		}
		else
		{
			$resource = Acl_Resource::byNameCheck (func_get_args ());
		}

		if ($resource)
		{
			$this->deattachResource ($resource);
		}
	}

	/**
	 * @desc Проверяет, имеет ли пользователь эту роль.
	 * @param User $user
	 * @return boolean
	 */
	public function userAttached (User $user)
	{
	    return $this->Acl_Role_Type->isUserAttached ($user, $this);
    }

}