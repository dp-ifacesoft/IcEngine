<?php

/**
 * Глобальный опшен
 * Получить в дату для элементов коллекции по первому изображению
 *
 * @author neon
 */
class Model_Option_First_Image extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function after ()
	{
		$modelName = $this->collection->modelName ();
		$ids = $this->collection->column (Model_Scheme::keyField ($modelName));
		$query = Query::instance ()
			->select ('id, rowId, smallUrl')
			->from ('Component_Image')
			->where ('table', $modelName)
			->where ('rowId', $ids)
			->group ('rowId');
		$images = DDS::execute ($query)->getResult ()->asTable ();
		if (!$images)
		{
			return;
		}
		$imageArray = array ();
		foreach ($images as $image)
		{
			$imageArray [$image ['rowId']] = $image;
		}
		foreach ($this->collection as $item)
		{
			if (!isset ($imageArray [$item->key ()]))
			{
				continue;
			}
			$item->data ('image', $imageArray[$item->key ()]);
		}
	}
}