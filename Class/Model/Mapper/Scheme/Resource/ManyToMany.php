<?php

Loader::load ('Model_Mapper_Scheme_Resource');

class Model_Mapper_Scheme_Resource_ManyToMany extends Model_Mapper_Scheme_Resource
{
	/**
	 * @see Model_Mapper_Scheme_Resource::add
	 */
	public function add ($item, $another_item = null)
	{
		Loader::load ('Model_Mapper_Scheme_Resource_ManyToMany_Link_Add');
		$link = new Model_Mapper_Scheme_Resource_ManyToMany_Link_Add ();
		$link->setResource ($this);
		$link->link (
			$item,
			$another_item,
			$this->_reference
		);
	}

	/**
	 * @see Model_Mapper_Scheme_Resource::add
	 */
	public function delete ($item, $another_item = null)
	{
		Loader::load ('Model_Mapper_Scheme_Resource_ManyToMany_Link_Delete');
		$link = new Model_Mapper_Scheme_Resource_ManyToMany_Link_Delete ();
		$link->setResource ($this);
		$link->link (
			$item,
			$another_item,
			$this->_reference
		);
	}
}