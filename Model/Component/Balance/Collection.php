<?php

/**
 *
 * @desc Коллекция балансов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Component_Balance_Collection extends Component_Single_Collection
{

	/**
	 * @desc Возвращает текущй баланс.
	 * @return float
	 */
	public function value ()
	{
		return $this->first ()->value;
	}

}