<?php

/**
 * Для выбора контента из заданной категории
 * @author goorus
 */
class Content_Option_Category extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where('Content_Category__id', $this->params['id']);
	}

}
