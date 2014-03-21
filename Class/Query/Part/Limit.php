<?php

/**
 *
 * @author markov(не очень)
 */
class Query_Part_Limit extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        if (isset($this->params['count']) || isset($this->params['offset'])) {
            $perPage = (int) $this->params ['count'];
            $offset = isset ($this->params ['offset']) ?
                $this->params ['offset'] : null;
        } else {
            $page = isset($this->params['page']) ?
                $this->params['page'] : 1;
            $perPage = $this->params['perPage'];
            $offset = ($page - 1) * $perPage;
        }
		$this->query->limit (
			$perPage,
			$offset
		);
	}
}