<?php

/**
 * Валидатор контроллера на предмет того, авторизован ли пользователь
 * 
 * @author morph
 */
class Controller_Validator_User_Authorized extends
    Controller_Validator_Abstract 
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $user = $this->getService('user')->getCurrent();
        if (!$user->key()) {
            return $this->accessDenied();
        }
    }
}