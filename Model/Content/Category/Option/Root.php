<?php
/**
 *
 * @desc Опция для выбора только родительских разделов
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Content_Category_Option_Root extends Model_Option
{
	public function after ()
	{
		$category_ids = $this->collection->column ('id');
		$query = Query::instance ()
			->select ('Content_Category.parentId')
			->from ('Content_Category')
			->where ('parentId', $category_ids);
		$parent_ids = array_unique (
			DDS::execute ($query)->getResult ()->asColumn ()
		);
		$query = Query::instance ()
			->select ('Content.Content_Category__id')
			->from ('Content')
			->where ('Content.Content_Category__id', $category_ids);
		$content_ids = array_unique (
			DDS::execute ($query)->getResult ()->asColumn ()
		);
		foreach ($this->collection as $i => $category)
		{
			if (
				!in_array ($category->key (), $parent_ids) &&
				!in_array ($category->key (), $content_ids)
			)
			{
				$this->collection->exclude ($i);
			}
		}
	}

	public function before ()
	{
		$this->query
			->where ('parentId', 0)
			->order ('title');
	}

}
