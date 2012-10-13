<?php

class Component_Comment_Option_User extends Model_Option
{
	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before ()
	{
		$this->query
			->leftJoin (
				'User',
				'Component_Comment.User__id=User.id'
			);
	}

	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function after ()
	{
		$attrs = !empty ($this->params ['attrs']) ?
			array_values ((array) $this->params ['attrs']) :
			array ();

		$avatar = !empty ($this->params ['avatar']) ?
			(bool) $this->params ['avatar'] :
			false;

		foreach ($this->collection as $item)
		{
			if ($avatar)
			{
				$ava = $item
					->User
						->component ('Avatar')
							->first ();

			    $item->data ('avatar', $ava);
			}
			for ($i = 0, $icount = count ($attrs); $i < $icount; $i++)
			{
				$item
					->User
						->attr ($attrs [$i]);
			}
		}
	}
}