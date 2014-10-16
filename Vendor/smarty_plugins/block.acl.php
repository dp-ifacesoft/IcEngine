<?php
/**
 * Блок, для которого необходима проверка прав доступа.
 * 
 * @param array $params Параметры.
 * @param string $content Код шаблона.
 * @param Smarty $smarty Экземпляр смарти.
 * @param boolean $repeat
 * 
 * @tutorial
 * {acl role="admin|moderator,manager"}Этот блок увидят только администратор и модератор-менеджер{/acl}
 * {acl auth=1}Этот блок увидят все авторизованные пользователи{/acl}
 */
function smarty_block_acl($params, $content, $smarty, &$repeat)
{
	if ($content) {
		return $content;
	}
	$access = false;
    $serviceLocator = IcEngine::serviceLocator();
    $user = $serviceLocator->getService('user')->getCurrent();
	if (isset($params['role'])) {
		$altRoles = explode('|', $params['role']);
		// пользователь должен иметь все роли одной из групп
		foreach ($altRoles as $roles) {
			$roles = explode(',', $roles);
			$rolesAccess = $user->hasRole($roles);
			if ($rolesAccess) {
				$access = true;
				break;
			}
		}
	}
	if (isset($params['auth'])) {
		$access |= ($params['auth'] == $user->key());
	}
	$repeat = $access;
}