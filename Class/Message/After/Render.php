<?php

/**
 * @desc Событие перед началом рендера из фронт контроллера.
 * @author Юрий
 *
 */
class Message_After_Render extends Message_Abstract
{
    
    public static function push ($view, array $params = array ())
    {
        IcEngine::$application->messageQueue->push (
        	'After_Render',
            array_merge (
                $params,
                array ('view'	=> $view)
            )
        );
    }
    
}