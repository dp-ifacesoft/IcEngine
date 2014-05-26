<?php
/**
 *
 * @desc Для выбора тегов по представлению в транслите.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Component_Tag_Option_Translit extends Model_Option
{

	/**
	 * @see Model_Collection_Option_Abstract::before ()
	 */
	public function before ()
	{
		$this->query
			->where ('translit', $this->params ['translit']);
	}

}