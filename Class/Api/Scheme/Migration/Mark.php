<?php

/**
 * Схема api для пометки миграций моделей
 * 
 * @author morph
 */
class Api_Scheme_Migration_Mark extends Api_Scheme_Abstract
{
    /**
     * @inheritdoc
     */
    protected $scheme = array(
        'set'      => array(
            'call'  => 'set',
            'args'  => array(
                'url', 'locationName', 'migrationName'
            )
        ),
        'get'      => array(
            'call'  => 'get',
            'args'  => array(
                'url', 'locationName', 'migrationName'
            )
        ),
    );
    
    /**
     * @inheritdoc
     */
    protected $transportName = 'Http';
}