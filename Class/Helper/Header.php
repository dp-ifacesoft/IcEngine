<?php
/**
 *
 * @desc Хелпер управления заголовоками
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Helper_Header
{
	/**
	 *
	 * @desc Код ошибки "ресурс перемещен постоянно"
	 * @var integer
	 */
	const E301 = 301;

	/**
	 * @desc Код ошибки "доступ закрыт"
	 * @var integer
	 */
	const E403 = 403;

	/**
	 * @desc Код ошибки "страница не найдена"
	 * @var integer
	 */
	const E404 = 404;

	/**
	 * @desc Код ошибки "страница удалена"
	 * @var integer
	 */
	const E410 = 410;

	/**
	 * @desc Флаг кодирования gzip
	 * @var string
	 */
	const CONTENT_ENCODING_GZIP = 'gzip';

	/**
	 * @desc Сообщения с ошибками
	 * @var array
	 */
	public static $statuses = array (
		self::E403 => array (
			"HTTP/1.0 403 Permission Denied",
			"Status: 403 Permission Denied"
		),
		self::E404 => array(
			"HTTP/1.0 404 Not Found",
			"Status: 404 Not Found"
		),
		self::E410 => array (
			"HTTP/1.0 410 Gone",
			"Status: 410 Gone"
		),
		self::E301 => array(
			"HTTP/1.1 301 Moved Remanently",
			"Status: 301 Moved Remanently"
		)
	);

	/**
	 * @desc Отправить статус в заголовке ответа
	 * @param integer $status Статус.
	 */
	public static function setStatus ($status, $set_code = true)
	{
		foreach (self::$statuses [$status] as $text)
		{
			if ($set_code)
			{
				header ($text, true, $status);
			}
			else
			{
				header ($text);
			}
		}
	}

	/**
	 * @desc Редирект по указанному адресу.
	 * К переданному $uri при необходимости будет добавлено имя сервера
	 * и "http://".
	 * @param string $uri
	 * @param integer $code [optional] Код редиректа.
	 * Например Helper_Header::E301
	 */
	public static function redirect ($uri, $code = null)
	{
		$full_uri =
			substr ($uri, 0, 7) == 'http://' ?
			$uri :
			('http://' . Request::host () . $uri);

		if (!headers_sent ())
		{
			if ($code)
			{
				header ("Location: $full_uri", true, $code);
			}
			else
			{
				header ("Location: $full_uri");
			}
		}
		else
		{
			self::jsRedirect ($uri);
		}
	}

	/**
	 * @desc Редирект через javascript.
	 * @param string $uri Адрес для редиректа
	 */
	public static function jsRedirect ($uri)
	{
		echo
			'<script type="text/javascript">window.location.href="' .
			$uri .
			'"</script>';
	}

}