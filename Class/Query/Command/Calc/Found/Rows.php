<?php

/**
 * Часть запроса "calcFoundRows"
 * 
 * @author morph
 */
class Query_Command_Calc_Found_Rows extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::CALC_FOUND_ROWS;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = true;
        return $this;
    }
}