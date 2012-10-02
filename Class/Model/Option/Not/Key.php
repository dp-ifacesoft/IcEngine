<?php

/**
 * Опшен получить кроме ИД
 *
 * @author neon
 */
class Model_Option_Not_Key extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		if (isset($this->params['key'])) {
			$ids = $this->params['key'];
			if (is_string ($ids)) {
				$ids = explode(',', $ids);
			}
			if (count($ids) < 1) {
				return;
			}
			$this->query
				->where($this->collection->modelName() . '.id NOT IN(?)', array($ids));
		}
	}
}