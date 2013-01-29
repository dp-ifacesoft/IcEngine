<?php

/**
 * Description of Asc
 *
 * @author markov
 */
class Query_Part_Order_Asc extends Query_Part 
{
	/**
	 * @inheritdoc
	 */
	public function query() 
	{	
		$this->query->order(array($this->params['field'] => Query::ASC));
	}
}
