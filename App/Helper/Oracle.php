<?php

namespace Ice;

/**
 *
 * @desc Оракул, обладает провидением.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Helper_Oracle
{

	/**
	 * @desc Провидение
	 * @param DateTime $time Интирисумое время
	 * @param array $params
	 * @return mixed Увиденное
	 */
	public static function foresee ($time, $params = array ())
	{
		$query = Query::instance ()
			->select ('facts')
			->from ('universe')
			->where ('time', $time);

		foreach ($params as $what => $how)
		{
			$query->where ($what, $how);
		}

		return Data_Source_Manager::getInstance (__CLASS__)->get ('Future')
			->execute (
				$query
			)->getResult ();
	}

}