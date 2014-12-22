<?php

/**
 * Запрос типа truncate table
 * 
 * @author morph, goorus
 */
class Query_Truncate_Table extends Query_Abstract
{
    /**
     * @inheritdoc
     */
	protected $type = Query::DELETE;
}