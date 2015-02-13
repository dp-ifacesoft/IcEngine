<?php
/**
 * Фильтр преобразует дату к логическому типу
 *
 * @author neon
 */
class Data_Filter_Boolean extends Data_Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		return (bool) $data;
	}
}