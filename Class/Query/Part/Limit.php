<?php

/**
 * Опшен для ограничения вывода
 *
 * @author neon
 */
class Query_Part_Limit extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        $perPage = (int) (isset($this->params['perPage']) ?
            $this->params['perPage'] : $this->params['count']);
        $offset = isset($this->params['offset']) ? $this->params['offset'] : 0;
        if (!isset($this->params['offset']) && isset($this->params['page'])) {
            $offset = $perPage * ($this->params['page'] - 1);
        }
		$this->query->limit($perPage, $offset);
	}
}