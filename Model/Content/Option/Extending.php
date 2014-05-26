<?php

/**
 * Расширение контента
 *
 * @author morph
 */
class Content_Option_Extending extends Model_Option
{
	public function before()
	{
		$model = $this->params['model'];
		$this->query
			->select($model . '.*')
			->innerJoin(
				$model,
				'Content.id=' . $model . '.id'
			)
			->where ('Content.extending', $model);
	}
}