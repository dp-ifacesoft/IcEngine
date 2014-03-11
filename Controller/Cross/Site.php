<?php

class Controller_Cross_Site extends Controller_Abstract
{
    
    public function test ()
    {
        echo '<CSDATABEGIN>' . json_encode ($_REQUEST);
        die ();
    }
    
}