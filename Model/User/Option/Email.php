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
        $locator = IcEngine::serviceLocator();
        $queryBuilder = $locator->getService('query');
        $email = $this->params['value'];
        $this->query->where(
            $queryBuilder
                ->where('email', $email)
                ->orWhere('login', $email)
        );
    }
}