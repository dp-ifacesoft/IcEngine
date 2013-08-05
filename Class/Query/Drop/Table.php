<?php

/**
 * Запрос типа drop table
 * 
 * @author morph, goorus
 */
class Query_Drop_Table extends Query_Abstract
{
    /**
     * @inheritdoc
     */
	protected $type = Query::DELETE;
}