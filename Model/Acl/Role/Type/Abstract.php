<?php

abstract class Acl_Role_Type_Abstract extends Model_Factory_Delegate
{
    
    /**
     * Проверяет отношение пользователя к роли.
     * @param User $user
     * @param Acl_Role $role
     * @return boolean
     */
    public function isUserAttached (User $user, Acl_Role $role)
    {
        if ($role->name == Acl_Role::GUEST_ROLE_NAME)
		{
			// Незарегистрированная посетитель сайта
			return ($user->id == 0);
		}

		// Зарегистрированный пользователь
		return Helper_Link::wereLinked ($role, $user);
    }
    
}