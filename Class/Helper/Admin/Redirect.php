<?php

/**
 * Хелпер редиректа при входе в админку
 *
 * @Service("helperAdminRedirect")
 * @author markov
 */
class Helper_Admin_Redirect extends Helper_Abstract
{
    /**
     * Ссылка для редиректа
     */
    public function uri($user)
    {
        return '/admin/';
    }
}
