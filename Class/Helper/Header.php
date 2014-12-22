<?php
/**
 *
 * Хелпер управления заголовоками
 * 
 * @author goorus, morph
 * @Service("helperHeader")
 */
class Helper_Header extends Helper_Abstract
{
	/**
	 * Код ошибки "ресурс перемещен постоянно"
	 * 
     * @var integer
	 */
	const E301 = 301;

	/**
	 * Код ошибки "доступ закрыт"
	 * 
     * @var integer
	 */
	const E403 = 403;

	/**
	 * Код ошибки "страница не найдена"
	 * 
     * @var integer
	 */
	const E404 = 404;

	/**
	 * Код ошибки "страница удалена"
	 * 
     * @var integer
	 */
	const E410 = 410;

	/**
	 * Флаг кодирования gzip
	 * 
     * @var string
	 */
	const CONTENT_ENCODING_GZIP = 'gzip';

	/**
	 * Сообщения с ошибками
	 * 
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
	 * Отправить статус в заголовке ответа
	 * 
     * @param integer $status Статус.
	 */
	public function setStatus($status, $setCode = true)
	{
		foreach (self::$statuses[$status] as $text) {
			if ($setCode) {
				header($text, true, $status);
			} else {
				header($text);
			}
		}
	}

	/**
	 * Редирект по указанному адресу.
	 * К переданному $uri при необходимости будет добавлено имя сервера
	 * и "http://".
	 * 
     * @param string $uri
	 * @param integer $code [optional] Код редиректа.
	 * Например Helper_Header::E301
	 */
	public function redirect($uri, $code = null)
	{
		$fullUri = substr ($uri, 0, 7) == 'http://' 
            ? $uri :
			('http://' . $this->getService('request')->host() . $uri);
		if (!headers_sent()) {
			if ($code) {
				header("Location: $fullUri", true, $code);
			} else {
				header("Location: $fullUri");
			}
		} else {
			$this->jsRedirect($uri);
		}
	}

	/**
	 * Редирект через javascript.
	 * 
     * @param string $uri Адрес для редиректа
	 */
	public function jsRedirect($uri)
	{
		echo '<script type="text/javascript">window.location.href="' . $uri .
			'"</script>';
	}
}