<?php
/**
 * 
 * @desc Опция для выбора только первой записи
 * @author Yury Shvedov
 * @package Ice_Vipgeo
 * 
 */
class Model_Option_First extends Model_Option
{

	public function before ()
	{
		$this->query->limit (1, 0);
	}
	
}
