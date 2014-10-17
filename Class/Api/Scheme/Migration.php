<?php

/**
 * Схема api для миграций
 * 
 * @author morph
 */
class Api_Scheme_Migration extends Api_Scheme_Abstract
{
    /**
     * @inheritdoc
     */
    protected $scheme = array(
        'next'      => array(
            'call'  => 'next',
            'args'  => array(
                'url'
            )
        ),
        'status'    => array(
            'call'  => 'status',
            'args'  => array(
                'url'
            )
        ),
        'sync'      => array(
            'call'  => 'sync',
            'args'  => array(
                'url', 'value'
            )
        )
    );
    
    /**
     * @inheritdoc
     */
    protected $transportName = 'Http';
}