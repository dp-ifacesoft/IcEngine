<?php

class Acl_Role_Collection extends Model_Collection
{
	
	/**
	 * Добавление ролей пользователю
	 * @param User $user
	 */
	public function attachUser (User $user)
	{
		$items = &$this->items ();
		foreach ($items as $item)
		{
			$item->attachUser($user);
		}
	}
	
	/**
	 * 
	 * @param User $user
	 */
	public function deattachUser (User $user)
	{
		$items = &$this->items ();
		foreach ($items as $item)
		{
			$item->deattachUser ($user);
		}
	}
	
	/**
	 * 
	 * @param Acl_Resource $resource
	 * @return boolean
	 */
	public function resourceAttached (Acl_Resource $resource)
	{
		$items = &$this->items ();
		foreach ($items as $item)
		{
			if ($item->resourceAttached ($resource))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 
	 * @param User $user
	 * @return boolean
	 */
	public function userAttached (User $user)
	{
		foreach ($this as $item)
		{
			/**
			 * @var Acl_Role $item
			 */
			if ($item->userAttached ($user))
			{
				return true;
			}
		}
		
		return false;
	}
	
}