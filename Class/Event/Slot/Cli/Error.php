<?php

/**
 * Слот для обработки ошибок cli
 * 
 * @author morph
 */
class Event_Slot_Cli_Error extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $params = $this->getParams();
        echo (isset($params['method']) 
            ? 'Error from "' . $params['method'] . '". ' : '') . 
            $params['message'] . PHP_EOL;
        echo 'Exiting...' . PHP_EOL;
        exit;
    }
}