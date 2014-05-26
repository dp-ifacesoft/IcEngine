<?php
/**
 * 
 * @desc Модель поддержки API ВКонтакте
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Api_Vkontakte extends Api_Abstract
{
	
	
	
	/**
	 * @desc access_token используется для авторизации пользователя
	 * http://vkontakte.ru/developers.php?oid=-1&p=%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F_%D1%81%D0%B0%D0%B9%D1%82%D0%BE%D0%B2
	 * @param string $code 
	 * @return array
	 */
	public function getAccessToken ($code)
	{
		$config = $this->config ();
		$uri = 'https://api.vkontakte.ru/oauth/access_token?' .
			'client_id=' . $config ['app_id'] .
			'&client_secret=' . $config ['app_secret'] .
			'&code=' . $code;
		$answer = file_get_contents ($code);
		return json_decode ($answer, true);
	}
	
}
