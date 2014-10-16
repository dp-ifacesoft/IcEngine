<?php
/**
 * 
 * @desc Задание ограничений на выбор.
 * @param integer $count 
 * @param integer $offset
 * @author Goorus
 * @package Ice_Vipgeo
 * 
 */
class Model_Option_Limit extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	protected $queryName = 'Limit';
}
