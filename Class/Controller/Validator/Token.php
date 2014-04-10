<?php

/**
 * Валидация того, что экшин может быть вызван только через ajax
 * 
 * @author morph
 */
class Controller_Validator_Token extends Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $controller = $this->context->getController();
        $input = $controller->getInput();
        $token = $input->receive('token');
        $tokenManager = $this->getService('tokenManager');
        $tourToken = $tokenManager->get('tour');
        if (!$tourToken->isValid($token)) {
            return $this->sendError('obsolete');
        }
        return true;
    }
}