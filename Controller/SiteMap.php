<?php

class Controller_SiteMap extends Controller_Abstract
{
    
    public function index ()
    {
        Loader::load ('SiteMap');
        $this->_output->send (array (
            'map'	=> SiteMap::asList ()
        ));
    }
    
}