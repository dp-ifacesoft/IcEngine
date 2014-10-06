<?php

class Acl_Role_Option_Role_Type extends Model_Option
{
	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before ()
	{
		$role_type_id = !empty ($this->params ['role_type_id']) ?
			$this->params ['role_type_id'] : 0;

		$this->query
			->where ('Acl_Role.Acl_Role_Type__id=?', $role_type_id);
	}
}