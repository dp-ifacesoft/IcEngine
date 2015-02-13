<?php

/**
 * Контроллер для смены окружения (Site_Location)
 * 
 * @author morph
 */
class Controller_Behavior extends Controller_Abstract
{
    /**
     * Изменить окружение
     * 
     * @Context("helperBehavior")
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function set($name, $context)
    {
        $context->helperBehavior->set($name);
    }
}