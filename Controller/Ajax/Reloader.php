<?php

/**
 * Описание Reloader
 *
 * @author Apostle
 */
class Controller_Ajax_Reloader extends Controller_Abstract
{
    /**
     * @Ajax
     * @param type $map
     * @return type
     */
    public function reload($map)
    {
        $ajaxReloader = $this->getService('ajaxReloader');
        $html =  $ajaxReloader->reload($map);
        $this->output->send([
            'data' =>[
                'reloadedHtml'  =>  $html,
                'success'   =>  true
            ]
        ]);
    }
}
