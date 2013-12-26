<?php

/**
 * Валидатор контроллера на предмет того, является ли пользователь консольным
 * 
 * @author morph
 */
class Controller_Validator_User_Cli extends
    Controller_Validator_Abstract 
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $user = $this->getService('user')->getCurrent();
        if ($user->key() >= 0) {
            return $this->accessDenied();
        }
        return true;
    }
}