<?php

/**
 * Запрос типа show
 * 
 * @author morph, goorus
 */
class Query_Show extends Query_Select
{
    /**
     * @inheritdoc
     */
    protected $type = Query::SHOW;
}