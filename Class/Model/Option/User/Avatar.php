<?php

/**
 * Опшен для получения аватары, для любого элемента
 *
 * @author neon
 */

class Model_Option_User_Avatar extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function after()
	{
		$userIDS = $this->collection->column ('User__id');
		$avatarArray = array ();
		$result = DDS::execute (
			Query::instance ()
				->select ('rowId, smallUrl')
				->from ('Component_Image')
				->where ('table', 'User')
				->where ('rowId', $userIDS)
				->where ('name', 'avatar')
		)->getResult ()->asTable ();
		foreach ($result as $row)
		{
			$avatarArray[$row['rowId']] = array ('smallUrl' => $row['smallUrl']);
		}
		foreach ($this->collection as $item)
		{
			if (isset ($avatarArray[$item->User__id]))
			{
				$item->data ('avatar', $avatarArray[$item->User__id]);
			}
		}
	}
}