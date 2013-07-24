<?php

/**
 * Dto для Data_Scheme
 *
 * @author morph
 */
class Data_Scheme_Dto extends Dto
{
    /**
     * @inheritdoc
     */
    protected static $defaults = array(
        'modelName'     => '',
        'info'          => array(),
        'fields'        => array(),
        'indexes'       => array(),
        'references'    => array(),
        'events'        => array(),
        'signals'       => array()
    );  
}