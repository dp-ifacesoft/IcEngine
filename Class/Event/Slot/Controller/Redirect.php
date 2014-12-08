<?php

/**
 * Редирект после завершения контроллера
 *
 * @author morph
 */
class Event_Slot_Controller_Redirect extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $params = $this->getParams();
        $buffer = $params['task']->getTransaction()->buffer();
        if (!empty($buffer['origin']) || !$buffer) {
            return;
        }
        $redirectUrl = !empty($params['redirect']) ? $params['redirect'] : 
            (!empty($buffer['redirect']) ? $buffer['redirect'] : null);
        if ($redirectUrl) {
            $serviceLocator = IcEngine::serviceLocator();
            $serviceLocator->getService('helperHeader')->redirect($redirectUrl);
        }
    }
}