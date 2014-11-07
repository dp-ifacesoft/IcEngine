<?php

/**
 * query part для Not_Empty
 *
 * @author Apostle
 */
class Query_Part_Not_Empty extends Query_Part
{
    /**
     * @inheritdoc
     */
    public function query()
    {
        if (!is_array($this->params['field'])) {
            $this->query->where($this->params['field'] . '!= ?', '');
        }
        else {
            foreach ($this->params['field'] as $field) {
                $this->query->where($field . '!= ?', '');
            }
        }
    }
}
