<?php

/**
 * Ресурс состояние для связи "многие-ко-многим"
 * 
 * @author morph
 */
class Model_Mapper_Scheme_Resource_ManyToMany extends 
    Model_Mapper_Scheme_Resource
{
	/**
	 * @see Model_Mapper_Scheme_Resource::add
	 */
	public function add($item, $anotherItem = null)
	{
		$link = new Model_Mapper_Scheme_Resource_ManyToMany_Link_Add();
		$link->setResource($this);
		$link->link($item, $anotherItem, $this->reference);
	}

	/**
	 * @see Model_Mapper_Scheme_Resource::add
	 */
	public function delete($item, $anotherItem = null)
	{
		$link = new Model_Mapper_Scheme_Resource_ManyToMany_Link_Delete();
		$link->setResource($this);
		$link->link($item, $anotherItem, $this->reference);
	}
}