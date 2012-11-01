<?php
/**
 *
 * @desc Класс для работы с HTTP запросом.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Request
{

	const NONE_IP = '0.0.0.0';

	public static $_params = array ();

	public static $post_charset = 'utf-8';
	public static $work_charset = 'utf-8';

	/**
	 * @desc Проверка формата входных данных
	 * @return boolean
	 */
	public static function altFilesFormat ()
	{
		if (empty ($_FILES))
		{
			return false;
		}

		$f = reset ($_FILES);
		return is_array ($f ['name']);
	}

	/**
	 * @desc Получить текущий хост.
	 * @return Ambigous <string, NULL>
	 */
	public static function host ()
	{
		return isset ($_SERVER ['HTTP_HOST'])
			? $_SERVER ['HTTP_HOST']
			: null;
	}

	/**
	 * @desc Получение параметра GET.
	 * @param string $name Имя параметра
	 * @param mixed $default Значение по умолчанию
	 * @return mixed
	 */
	public static function get ($name, $default = false)
	{
		return isset ($_GET [$name]) ? $_GET [$name] : $default;
	}

	/**
	 * @desc IP источника запроса
	 * @return string
	 */
	public static function ip ()
	{
		if (isset ($_SERVER ['HTTP_X_REAL_IP']))
		{
			return $_SERVER ['HTTP_X_REAL_IP'];
		}

		return isset ($_SERVER ['REMOTE_ADDR']) ?
			$_SERVER ['REMOTE_ADDR'] : self::NONE_IP;
	}

	/**
	 *
	 * @return boolean
	 */
	public static function isAjax ()
	{
		return (
			isset ($_SERVER ['HTTP_X_REQUESTED_WITH']) &&
			$_SERVER ['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
		);
	}

	/**
	 * @desc Проверяет, что скрипт был вызван через консоль.
	 * @return boolean true, если скрипт был вызван из командной строки,
	 * иначе - false.
	 */
	public static function isConsole ()
	{
		return isset ($_SERVER ['argv'], $_SERVER ['argc']);
	}

	/**
	 * @desc Проверяет, передены ли файлы от пользователя.
	 * @return boolean
	 */
	public static function isFiles ()
	{
		return !empty ($_FILES);
	}

	/**
	 * @desc Проверяет, переданы ли GET параметры.
	 * @return boolean
	 */
	public static function isGet ()
	{
		return !empty ($_GET) || (
			isset ($_SERVER ['REQUEST_METHOD']) &&
			$_SERVER ['REQUEST_METHOD'] == 'GET'
		) ;
	}

	/**
	 * @desc Проверяет, был ли это запрос через JsHttpRequest
	 * @return boolean
	 */
	public static function isJsHttpRequest ()
	{
		global $JsHttpRequest_Active;

		return (
			isset ($_SERVER ['REQUEST_METHOD']) &&
			$_SERVER ['REQUEST_METHOD'] == 'POST' &&
			isset ($JsHttpRequest_Active) &&
			$JsHttpRequest_Active
		);
	}

	/**
	 * @desc Проверяет, что это был POST запрос
	 * @return boolean
	 */
	public static function isPost ()
	{
		return (
			isset ($_SERVER ['REQUEST_METHOD']) &&
			$_SERVER ['REQUEST_METHOD'] == 'POST'
		);
	}

	public static function isSsi ()
	{
		return (
                        isset ($_SERVER ['REQUEST_METHOD']) &&
                        $_SERVER ['REQUEST_METHOD'] == 'ssi'
                );

	}

	/**
	 * @desc Получение или установка параметра.
	 * @param string $key Название параметра.
	 * @param string $value [optional] Значение.
	 * Если передано значение, до оно будет установлено.
	 * @return string|null Если указано только название параметра, то
	 * возращается его значение.
	 */
	public static function param ($key)
	{
		if (func_num_args () > 1)
		{
			self::$_params [$key] = func_get_arg (1);
		}
		else
		{
			return isset (self::$_params [$key]) ? self::$_params [$key] : null;
		}
	}

	/**
	 * @desc Возвращает все параметры адресной строки.
	 * Это не GET параметры, а параметры, определяемые роутом.
	 * @return array
	 */
	public static function params ()
	{
		return self::$_params;
	}

	/**
	 * @desc Получение параметра POST.
	 * @param string $name Имя параметра
	 * @param mixed $default Значение по умолчанию
	 * @return mixed
	 */
	public static function post ($name, $default = false)
	{
		if (isset($_POST [$name]))
		{
			if (self::$work_charset == self::$post_charset)
			{
				return $_POST [$name];
			}
			else
			{
				return @iconv (
					self::$post_charset,
					self::$work_charset,
					$_POST [$name]
				);
			}
		}
		else
		{
			return $default;
		}
	}

	/**
	 * @desc Возвращает переданные скрипту id из $_REQUEST['id'] и $_REQUEST['ids']
	 * @return array
	 */
	public static function postIds ()
	{
		$item_ids = array ();

		if (isset ($_REQUEST ['id']))
		{
			$item_ids = array ((int) $_REQUEST ['id']);
		}
		elseif (isset ($_REQUEST ['ids']))
		{
			$item_ids = $_REQUEST ['ids'];
			if (!is_array ($item_ids))
			{
				$item_ids = explode (',', $item_ids);
			}
		}
		return $item_ids;
	}

	/**
	 *
	 * @param string $name Имя поля
	 * @return PostedFile|false
	 */
	public static function file ($name)
	{
		if (isset($_FILES [$name]) && !empty($_FILES [$name]['name']))
		{
			return new Request_File($_FILES [$name]);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @desc Возвращает объект переданного файла.
	 * @param integer $index Индекс.
	 * @return Request_File Переданный файл.
	 * 		Если файлов меньше, чем указанный индекс - null.
	 */
	public static function fileByIndex ($index)
	{
		$files = array_values ($_FILES);

		if (!isset ($files [$index]))
		{
			$f = '@file:' . $index;
			if (isset ($_POST [$f]))
			{
				return new Request_File_Test ($_POST [$f]);
			}

			if (isset ($_POST ['params'], $_POST ['params'][$f]))
			{
				return new Request_File_Test ($_POST ['params'][$f]);
			}

			return null;
		}

		if (is_array ($files [$index]['name']))
		{
			$file = array ();
			foreach ($files [$index] as $field => $values)
			{
				$file [$field] = reset ($values);
			}

			return new Request_File ($file);
		}

		return new Request_File ($files [$index]);
	}

	/**
	 * @desc Возвращает массив объектов переданных файлов.
	 * @return array Request_File
	 */
	public static function files ()
	{
		$result = array();
		foreach ($_FILES as $name => $file)
		{
			$result[$name] = new Request_File ($file);
		}
		return $result;
	}

	/**
	 * @desc Возвращает количество переданных в запросе файлов.
	 * @return integer Количество переданных файлов.
	 */
	public static function filesCount ()
	{
		return count ($_FILES);
	}

	/**
	 * @desc Возвращает часть адреса без параметров GET.
	 * @return string Часть URI до знака "?"
	 */
	public static function uri ($without_get = true)
	{
		if (!isset ($_SERVER ['REQUEST_URI']))
		{
			return '/';
		}

		$url = $_SERVER ['REQUEST_URI'];

		if ($without_get)
		{
			$p = strpos ($url, '?');
			if ($p !== false)
			{
				return substr ($url, 0, $p);
			}
		}
		return $url;
	}

	/**
	 * @desc Возвращает часть запроса GET
	 * @return string Часть URI после знака "?"
	 */
	public static function stringGet ()
	{
		if (!isset ($_SERVER ['REQUEST_URI']))
		{
			return '';
		}

		$url = $_SERVER ['REQUEST_URI'];
		$p = strpos ($url, '?');

		if ($p !== false)
		{
			return substr ($url, $p + 1);
		}

		return '';
	}

    /**
     * Получить поддомен
     *
     * @return string
     */
    public static function subdomain($default = null)
    {
        $host = self::host();
        $main = Helper_Uri::mainDomain();
        $subdomain = trim(str_replace($main, '', $host), '.');
        return $subdomain ?: $default;
    }

	/**
	 * @return string
	 */
	public static function referer ()
	{
		return isset ($_SERVER ['HTTP_REFERER'])
			? $_SERVER ['HTTP_REFERER'] : '';
	}

	/**
	 * @return string
	 */
	public static function requestMethod ()
	{
		return isset ($_SERVER ['REQUEST_METHOD'])
			? $_SERVER ['REQUEST_METHOD'] : 'GET';
	}

	/**
	 * @desc Возвращает название сервера.
	 * В зависимости от настроек nginx может вернуть "*.server.com"
	 * @return string
	 */
	public static function server ()
	{
		return isset ($_SERVER ['SERVER_NAME'])
			? $_SERVER ['SERVER_NAME'] : '';
	}

	/**
	 * @return string
	 */
	public static function sessionId ()
	{
		if (!class_exists ('Session_Manager'))
		{
			Session_Manager::init ();
		}

		if (isset ($_COOKIE ['PHPSESSID']))
		{
			session_id ($_COOKIE ['PHPSESSID']);
		}
		elseif (isset ($_GET ['PHPSESSID']))
		{
			session_id ($_GET ['PHPSESSID']);
		}

		if (!isset ($_COOKIE))
		{
			$_COOKIE = array ();
		}

		if (!isset ($_SESSION))
		{
			session_start ();
			//$_SESSION ['session_start'] = time ();
			//setcookie ('PHPSESSID', session_id (), 3600, '.vipgeo.ru', '/');
			//$_COOKIE ['PHPSESSID'] = session_id ();
			//print_r ($_SESSION); print_r ($_COOKIE);
		}

		if (!isset ($_COOKIE ['PHPSESSID']))
		{
			setcookie ('PHPSESSID', session_id (), 3600);
                        $_COOKIE ['PHPSESSID'] = session_id ();

		}

		return session_id ();
	}

}
