<?php

/**
 * По не User
 * 
 * @author markov
 */
class Query_Part_Not_User extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        if (!is_array($this->params['id'])) {
			$this->query->where('User__id != ?', $this->params['id']);
		} else {
			$this->query->where('User__id NOT IN (?)', $this->params['id']);
		}
	}
}