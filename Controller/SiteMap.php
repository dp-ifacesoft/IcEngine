<?php

class Controller_SiteMap extends Controller_Abstract
{

    public function index ()
    {
        $this->_output->send (array (
            'map'	=> SiteMap::asList ()
        ));
    }

}