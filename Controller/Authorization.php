<?php

/**
 * Контроллер авторизации
 * 
 * @author morph
 */
class Controller_Authorization extends Controller_Abstract
{
    /**
     * Выход
     * 
     * @Route("/logout/")
     * @Context("helperHeader")
     */
    public function logout($context)
    {
        $context->userSession->getCurrent()->delete();
        $context->helperHeader->redirect('/');
    }
}