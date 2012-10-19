<?php

class Data_Validator_Captcha_Auto_Code extends Data_Validator_Abstract
{

	const FAIL = 'fail';

	public function validate ($data)
	{
		if (
			!isset ($data, $_SESSION [Helper_Captcha::SF_AUTO_CODE]) ||
			$_SESSION [Helper_Captcha::SF_AUTO_CODE] != $data
		)
		{
			return __CLASS__ . '/' . self::FAIL;
		}

		unset ($_SESSION [Helper_Captcha::SF_AUTO_CODE]);

		return true;
	}

}