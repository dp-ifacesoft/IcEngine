<?php

/**
 * Запрос типа create table
 * 
 * @author morph, goorus
 */
class Query_Create_Table extends Query_Alter_Table
{
    /**
     * @inheritdoc
     */
	protected $type = Query::INSERT;
}