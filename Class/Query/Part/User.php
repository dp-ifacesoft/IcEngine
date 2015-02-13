<?php

/**
 *
 * @author markov
 */
class Query_Part_User extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.User__id',
			$this->params['id']);
        if (isset($this->params['notZero'])) {
            $this->query->where($this->modelName . '.User__id != 0');
        }
	}
}