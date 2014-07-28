<?php

/**
 * Делает LIKE по всем перечисленным полям и значениям
 * @author viktor
 */
class Query_Part_Compare extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        foreach ($this->params['fields'] as $key=> $value) {
            if(!empty($key) && !empty($value)) {
                $this->query->where(
                    $this->modelName .'.'. $key. " LIKE '%" . $value . "%'"
                );
            }
        }
	}
}