<?php
/**
 *
 * @desc Помощник работы с сетью.
 * @author Гурус
 * @package IcEngine
 *
 */
class Helper_Network
{

	/**
	 * Вызывает получение страницы на сервере, но ответа не дожидается.
	 *
	 * @param string	$host
	 * 		Хост
	 * @param string	$page
	 * 		Страница
	 * @param array		$paramGet
	 * 		GET параметры запроса
	 * @param array		$paramPost
	 * 		POST параметры запроса
	 * @param string	$refer
	 * 		Страница - реферер
	 * @param string	$userAgent
	 * 		Представиться браузером
	 */
	public static function callUnresultedPage ($host = 'localhost', $page = '/index.php',
		$gets = array(), $posts = array(),
		$refer = 'http://localhost', $userAgent = 'Mozilla 4.0')
	{
		// если делать через сокеты, тогда соединения будеть жить пока
		// работает скрипт, либо пока оно не оборвется, таймаутом например..

	//	$host="www.vasya.com";
	//	$refer="http://localhost";
	//	$zap="/b.php?blabla=123";
	//	$fp=fsockopen($host,80);
	//	$get="GET $zap HTTP/1.1\r\nHost: $host\r\nReferer: $refer\r\nUser-Agent: Mozilla 4.0\r\n\r\n";
	//	fwrite($fp,$get);
	//	fclose($fp);

		// если запускаемый скрипт должен работать в фоне и запускающий
		// скрипт не должен получать данные с него (тупо запустить и все),
		// то лучше воспользоватся таким кодом:
	//	pclose(popen('/usr/bin/php /home/user/httpdocs/script.php >> /dev/null &', 'r'));

		// Обработка GET параметров
		if ($gets)
		{
			$req_get = array();
			foreach ($gets as $k => $v)
			{
				$req_get[] = $k . '=' . urlencode ($v);
			}
			$req_get = $page . '?' . implode ('&', $req_get);
		}
		else
		{
			$req_get = $page;
		}

		// Обработка POST параметров
		if ($posts)
		{
			$req_post = array();
			foreach ($posts as $k => $v)
			{
				$req_post[] = $k . '=' . $v;
			}
			$req_post = '?' . implode ('&', $req_post);
		}

		// Запрос
		$req =
			"GET $req_get HTTP/1.1\r\n" .
			"Host: $host\r\n" .
			"Referer: $refer\r\n " .
			"User-Agent: $userAgent\r\n\r\n";

		$fp = fsockopen($host, 80);
		fwrite($fp, $req);
		fclose($fp);
	}

	public static function headers ($url)
	{
		if (!$url)
		{
			return null;
		}
		$matches = array ();
		preg_match_all ('/http:\/\/(.*?)\/(.*?)$/', $url, $matches);
		if (!$matches || !$matches [1] || !$matches [2])
		{
			return null;
		}
		$hostname = $matches [1][0];
		$path = $matches [2][0];
		$handle = fsockopen ($hostname, 80, $errno, $errstr, 10);
		if (!$handle)
		{
			return null;
		}
		$headers = 'GET /'.ltrim ($path, '/').' HTTP/1.0'."\r\n";
		$headers .= 'Host: '.$hostname."\r\n";
		$headers .= 'Connection: Close'."\r\n\r\n";
		fwrite ($handle, $headers);
		$headers = array ();
		while ($line = fgets($handle, 1024))
		{
			if (!trim ($line))
			{
				return $headers;
			}
			$headers [] = $line;
		}
		fclose ($handle);
		return $headers;
	}

	public static function post (
		$url,
		$postdata,
		$cookie_dir,
		$uagent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)'
	)
	{
	  $ch = curl_init( $url );
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	  curl_setopt($ch, CURLOPT_ENCODING, "");
	  curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
	  curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	  curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	  curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_dir);
	  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_dir);

	  $content = curl_exec( $ch );
	  $err     = curl_errno( $ch );
	  $errmsg  = curl_error( $ch );
	  $header  = curl_getinfo( $ch );

	  curl_close( $ch );

	  $header['errno']   = $err;
	  $header['errmsg']  = $errmsg;
	  $header['content'] = $content;

	  return $header;
	}

	/**
	 * Загружает удаленный файл по указанному пути.
	 *
	 * @param string $url
	 * 		Ссылка на файл
	 * @param string $dst_file
	 * 		Путь для сохранения
	 * @return boolean
	 * 		true, если загрузка завершена успешно, иначе false.
	 */
	public static function wgetFile ($url, $dst_file)
	{
		$fsrc = fopen ($url, "r");
		if (!$fsrc)
		{
			return false;
		}

		$fdst = fopen($dst_file, "w");
		if (!$fdst)
		{
			return false;
		}

		$block_size = 64 * 1024;
		while (!feof ($fsrc))
		{
			$data = fread ($fsrc, $block_size);
			fwrite($fdst, $data);
		}

		fclose($fdst);
		fclose($fsrc);

		return true;
	}

	/**
	 * Получение содержимого страницы
	 *
	 * @param string $host
	 * 		Хост
	 * @param string $page
	 * 		Страница
	 * @param array $gets
	 * 		GET параметры запроса
	 * @param array $posts
	 * 		POST параметры запроса
	 * @param string $refer
	 * 		Страница - реферер
	 * @param string $userAgent
	 * 		Представиться браузером
	 */
	public static function wgetPageContent($host = 'localhost', $page = '/index.php',
		array $gets = array(), array $posts = array(),
		$refer = 'http://www.yandex.ru', $userAgent = 'Mozilla/5.0 (X11; Linux x86_64; rv:10.0.6) Gecko/20100101 Firefox/10.0.6 Iceweasel/10.0.6')
	{
		// Обработка GET параметров
		if ($gets)
		{
			$req_get = array();
			foreach ($gets as $k => $v)
			{
				$req_get[] = $k . '=' . urlencode ($v);
			}
			$req_get = $page . '?' . implode ('&', $req_get);
		}
		else
		{
			$req_get = $page;
		}

		// Обработка POST параметров
		if ($posts)
		{
			$req_post = array();
			foreach ($posts as $k => $v)
			{
				$req_post[] = $k . '=' . $v;
			}
			$req_post = '?' . implode ('&', $req_post);
		}

		// Запрос
		$req =
			"GET $req_get HTTP/1.1\r\n" .
			"Host: $host\r\n" .
			"Referer: $refer\r\n " .
			"User-Agent: $userAgent\r\n\r\n";

		$fp = fsockopen($host, 80, $errno, $errstr, 1);

		// отправка данных
		fputs($fp, $req);
		// Чтение данных
		$query = "";
		while (!feof($fp))
		{
			$query .= fread($fp, 1048576);
		}
		fclose($fp);

		return $query;
	}

}