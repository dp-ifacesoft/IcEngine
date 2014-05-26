<?php

class Link_Option_From extends Model_Option
{
    /**
     *
     * @param Model_Collection $collection
     * @param Query $query
     * @param array $params
     * 		$params['table']
     * 		$params['rowId']
     */
    public function before ()
    {
        $this->query
            ->where ('Link.fromTable=?', $this->params ['table'])
            ->where ('Link.fromRowId=?', $this->params ['rowId']);
    }

}