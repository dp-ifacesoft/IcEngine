<?php

interface Object_Interface
{
	
	/**
	 * Помещение объекта в пул.
	 */
	public function free ();
	
	/**
	 * Сброс всех свойств объекта.
	 * После выполнения этого метода, объект должен быть
	 * идентичен вновь созданному.
	 */
	public function reset ();
	
}