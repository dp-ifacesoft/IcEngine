<?php

/**
 * Опшен для получения юзера по email'у
 *
 * @author neon
 */
class User_Option_Email extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $email = $this->params['value'];
        $this->query
            ->where('email', $email)
            ->orWhere('login', $email);
    }
}