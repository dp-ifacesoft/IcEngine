<?php

/**
 * @desc Количество комментариев
 */
class Model_Option_Comment_Count extends Model_Option
{
	public function after ()
	{
		$model_name = $this->collection->modelName ();
		$key_field = Model_Scheme::keyField ($model_name);
		$ids = $this->collection->column ($key_field);
		$query = Query::instance ()
			->select ('COUNT(*) as q, rowId')
			->from ('Component_Comment')
			->where ('table', $model_name)
			->where ('rowId', $ids)
			->where ('show', 1);
		$comment_counts = DDS::execute ($query)->getResult ()->asTable ();
		if (!$comment_counts)
		{
			return;
		}
		foreach ($comment_counts as $count)
		{
			$item = $this->collection->filter (array (
				$key_field	=> $count ['rowId']
			))->first ();
			if (!$item)
			{
				continue;
			}
			$item->data ('comment_count', $count ['q']);
		}
	}
}