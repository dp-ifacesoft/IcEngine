<?php

class Data_Validator_Registration_Ip_Limit extends Data_Validator_Abstract
{
	
	const FAIL			= 'fail'; // Лимит на 1 ip
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Validator_Abstract::validate()
	 */
	public function validate ($data)
	{
		$regs = Model_Collection_Manager::byQuery (
			'Registration',
			Query::instance ()
			->where ('day', Helper_Date::eraDayNum ())
			->where ('ip', Request::ip ())
		);
		
		if ($regs->count () >= Registration::config ()->ip_day_limit)
		{
			return __CLASS__ . '/' . self::FAIL;
		}
		
		return true;
	}
    
}