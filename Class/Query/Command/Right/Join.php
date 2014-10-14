<?php

/**
 * Часть запроса "rightJoin"
 * 
 * @author morph
 */
class Query_Command_Right_Join extends Query_Command_From
{
    /**
     * @inheritdoc
     */
    public function create($data)
    {
         $this->data = $this->helper()->join(
            $data[0], Query::RIGHT_JOIN, $data[1]
        );
        return $this;
    }
}