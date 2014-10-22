<?php
/**
 * Description of Asc
 *
 * @author markov
 */
class Query_Part_Order_Desc extends Query_Part 
{
	/**
	 * @inheritdoc
	 */
	public function query() 
	{	
		foreach ((array) $this->params['field'] as $field) {
            $this->query->order(array($field => Query::DESC));
        }
	}
}