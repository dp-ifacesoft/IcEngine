<?php
/**
 * @desc Блок, для которого необходима проверка прав доступа.
 * @param array $params Параметры.
 * @param string $content Код шаблона.
 * @param Smarty $smarty Экземпляр смарти.
 * @param boolean $repeat
 * 
 * @tutorial
 * {acl role="admin|moderator,manager"}Этот блок увидят только администратор и модератор-менеджер{/acl}
 * {acl auth=1}Этот блок увидят все авторизованные пользователи{/acl}
 */
function smarty_block_acl ($params, $content, $smarty, &$repeat)
{
	if ($content)
	{
		return $content;
	}
	
	$access = false;
	
	if (isset ($params ['role']))
	{
		$alt_roles = explode ('|', $params ['role']);
		
		// пользователь должен иметь все роли одной из групп
		foreach ($alt_roles as $roles)
		{
			$roles = explode (',', $roles);
			$roles_access = true;
			foreach ($roles as $role)
			{
				$role = Acl_Role::byName ($role);
				if (!User::getCurrent ()->hasRole ($role))
				{
					// Пользователь не имеет указанной роли
					$roles_access = false;
					break;
				}
			}
			
			if ($roles_access)
			{
				$access = true;
				break;
			}
		}
	}
	
	if (isset ($params ['auth']))
	{
		$access |= ($params ['auth'] == User::authorized ());
	}

	$repeat = $access;
}