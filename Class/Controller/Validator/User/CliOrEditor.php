<?php

/**
 * Валидатор контроллера на предмет того, является ли пользователь консольным
 * или редактором
 *
 * @author neon
 */
class Controller_Validator_User_CliOrEditor extends
    Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $user = $this->getService('user')->getCurrent();
        if ($user->key() >= 0 && !$user->hasRole('editor')) {
            return $this->accessDenied();
        }
    }
}