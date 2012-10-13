<?php

/**
 * @desc Расширение контента
 */
class Content_Option_Extending extends Model_Option
{
	public function before ()
	{
		if (!isset ($this->params ['model']))
		{
			return;
		}
		$model = $this->params ['model'];
		$this->query
			->select ($model . '.*')
			->innerJoin (
				$model,
				'Content.id=' . $model . '.id'
			)
			->where ('Content.extending', $model);
	}
}