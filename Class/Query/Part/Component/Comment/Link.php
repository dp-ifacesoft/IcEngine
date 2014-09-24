<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Link
 *
 * @author markov
 */
class Query_Part_Component_Comment_Link extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		if (isset ($this->params['table']))
		{
			$this->query
				->where ('table', $this->params['table']);
		}
		if (isset ($this->params['rowId']))
		{
			$this->query
				->where ('rowId', $this->params['rowId']);
		}
	}
}
