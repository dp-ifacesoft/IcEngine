<?php

/**
 * Часть запроса "innerJoin"
 * 
 * @author morph
 */
class Query_Command_Inner_Join extends Query_Command_From
{
    /**
     * @inheritdoc
     */
    public function create($data)
    {
         $this->data = $this->helper()->join(
            $data[0], Query::INNER_JOIN, $data[1]
        );
        return $this;
    }
}