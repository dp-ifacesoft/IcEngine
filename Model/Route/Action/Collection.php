<?php

class Route_Action_Collection extends Model_Collection
{

    /**
     * @return Controller_Action_Collection
     */
    public function controllerActions ()
    {
        $result = new Controller_Action_Collection ();

        foreach ($this as $route_action)
        {
            $result->add ($route_action->Controller_Action);
        }

        return $result;
    }

}