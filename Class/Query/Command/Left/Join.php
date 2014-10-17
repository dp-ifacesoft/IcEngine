<?php

/**
 * Часть запроса "leftJoin"
 * 
 * @author morph
 */
class Query_Command_Left_Join extends Query_Command_From
{
    /**
     * @inheritdoc
     */
    public function create($data)
    {
         $this->data = $this->helper()->join(
            $data[0], Query::LEFT_JOIN, $data[1]
        );
        return $this;
    }
}