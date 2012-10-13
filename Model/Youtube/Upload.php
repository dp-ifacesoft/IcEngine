<?php
/**
 * 
 * @desc Загружаемое на ютуб видео
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Youtube_Upload extends Model
{
	
	const _CPREF_AUTH_TOKEN = 'YoutubeAuthToken_';

	/**
	 * 
	 * @var string
	 */
	const TABLE = 'youtube_upload';
	
	/**
	 * @desc Время жизни ключа авторизации на ютубе
	 * @var integer
	 */
	const TOKEN_EXPIRATION = 3600;
	
	/**
	 * @desc Название зарегистрированного на ютубе сервиса
	 * @var string
	 */
	protected static $_service = '';
	
	/**
	 * @desc Настройки текущего сервиса
	 * @var array
	 */
	protected static $_currentConfig = array ();
	
	/**
	 * @desc Получение ключа авторизации сервиса на ютубе
	 * @return string|false
	 */
	protected static function _authToken ()
	{
		$cache = Registry::sget (self::_CPREF_AUTH_TOKEN . self::$_service);
		
		if (
			$cache && $cache ['a'] && $cache ['v'] &&
			($cache ['a'] + self::TOKEN_EXPIRATION < time ())
		)
		{
			return $cache ['v'];
		}
		
		$eq = 
			'accountType=HOSTED_OR_GOOGLE&Email=' . self::$_currentConfig ['email'] . 
			'&Passwd=' . self::$_currentConfig ['password'] . 
			'&service=youtube&source=' . self::$_currentConfig ['api_name'];
		
		$fp = fsockopen ("ssl://www.google.com", 443, $errno, $errstr, 20);
		if ($fp)
		{
			$request  = "POST /youtube/accounts/ClientLogin HTTP/1.0\r\n";
			$request .= "Host: www.google.com\r\n";
			$request .= "Content-Type:application/x-www-form-urlencoded\r\n";
			$request .= "Content-Length: ".strlen ($eq)."\r\n";
			$request .= "\r\n\r\n";
			$request .= $eq;
			fwrite ($fp, $request, strlen ($request));
			$response = '';
			while (!feof ($fp))
			{
				$response .= fread ($fp, 8192);
			}
			fclose ($fp);
		}
		else
		{
			trigger_error (
				'Не удалось связаться с сервером авторизации.',
				E_USER_WARNING
			);
			return false;
		}
		
		preg_match ("!(.*?)Auth=(.*?)\n!si", $response, $ok);
		
		if (!isset ($ok[2]))
		{
			trigger_error (
				'No auth key recieved from youtube.',
				E_USER_WARNING
			);
		}
		
		$cache = array(
			'a'	=> time (),
			'v'	=> $ok[2]
		);
		Registry::set (self::_CPREF_AUTH_TOKEN . self::$_service, $cache);
		
		return $cache ['v'];
	}
	
	/**
	 * Загрузка конфига ютуб сервиса.
	 * @param string $service
	 */
	protected static function _loadConfig ($service)
	{
		$config = Config_Manager::get (__CLASS__);
		$config = $config->__toArray ();
		
		if (!$config || !isset ($config [$service]))
		{
			trigger_error (
				"Youtube config for $service not found.",
				E_USER_WARNING
			);
			return false;
		}

		self::$_service = $service;
		self::$_currentConfig = $config [$service];
	}
	
	/**
	 * @desc 
	 * @param string $code Код
	 * @return Youtube_Upload
	 */
	public static function byCode ($code)
	{
		$data = DDS::execute (
			Query::instance ()
			->select ('*')
			->from (__CLASS__)
			->where ('code=?', $code)
		)->getResult ()->asRow ();
		
		if ($data)
		{
		    return new self ($data);
		}
		else
		{
		    return null;
		}
	}
	
	/**
	 * @desc Инициализация новой загрузки
	 * @param string $service Название сервиса на ютубе.
	 * @return array|false array [token, url] в случае успеха, иначе false.
	 */
	public static function newUpload ($service)
	{
		self::_loadConfig ($service);
		$auth_token = self::_authToken ();
		
		$data = 
			"<?xml version='1.0'?>\r\n" .
			"<entry xmlns='http://www.w3.org/2005/Atom' " .
			"xmlns:media='http://search.yahoo.com/mrss/' " .
			"xmlns:yt='http://gdata.youtube.com/schemas/2007'>" .
				"<media:group>" .
					"<media:title type='plain'>Vipgeo.ru</media:title>" .
					"<media:description type='plain'>Видео на vipgeo.ru</media:description>" .
					"<media:category scheme='http://gdata.youtube.com/schemas/2007/categories.cat'>Film</media:category>" .
					"<media:keywords>Отдых Туризм</media:keywords>" .
				"</media:group>" .
			"</entry>";
		
		$fp = fsockopen ("gdata.youtube.com", 80, $errno, $errstr, 20);
		if ($fp)
		{
			$request  = "POST /action/GetUploadToken HTTP/1.1\r\n";
			$request .=	"Host: gdata.youtube.com\r\n";
			$request .=	"Content-Type: application/atom+xml; charset=UTF-8\r\n";
			$request .=	"Content-Length: " . strlen ($data) . "\r\n";
			$request .=	"Authorization: GoogleLogin auth=$auth_token\r\n";
			$request .= "X-GData-Client: " . self::$_currentConfig ['api_name'] . "\r\n";
			$request .= "X-GData-Key: key=" . self::$_currentConfig ['api_key'] . "\r\n";
			$request .= "\r\n";
			$request .= $data . "\r\n";
			socket_set_timeout ($fp, 10);
			
			fputs ($fp, $request, strlen ($request));
			$response = fread ($fp, 16384);
		//	echo '<pre>'; var_dump($response); echo '</pre>';
			fclose ($fp);
			
			$p1 = strpos ($response, '<url>');
			if ($p1 === false)
			{
				trigger_error ('Не удалось связаться с сервером youtube.', E_USER_WARNING);
				return false;
			}
			$p1 += strlen ('<url>');
			$p2 = strpos ($response, '</url>', $p1);
			$upload_url = substr ($response, $p1, $p2 - $p1);
			
			$p1 = strpos ($response, '<token>') + strlen ('<token>');
			$p2 = strpos ($response, '</token>', $p1);
			$upload_token = substr ($response, $p1, $p2 - $p1);
		}
		else
		{
			trigger_error ('Не удалось связаться с сервером youtube.', E_USER_WARNING);
			return false;
		}
		
		return array (
			'token'	=> $upload_token,
			'url'	=> $upload_url
		);
	}
	
	/**
	 * @desc Возвращает адрес для загрузки видео.
	 * В конец будет добавлен GET параметр для колбэка после загрузки.
	 */
	public function uploadUrl ()
	{
//		Длинна запроса получается > 255 символов,
//		исползуем алиас 
//		$nexturl = 
//			'http://' . $_SERVER['SERVER_NAME'] . '/' .
//			'YoutubeUpload/sysYtCallback?uc=' . $this->code;
		
		// Алиас
		$nexturl = 
			'http://' . $_SERVER ['SERVER_NAME'] . '/' .
			'yc/?uc=' . $this->code;
		
		$url = 
			$this->url .
			(strpos ($this->url, '?') ? '&' : '?') . 
			'nexturl=' . urlencode ($nexturl);
		
		return $url;
	}
	
}