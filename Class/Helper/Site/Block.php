<?php
/**
 *
 * @desc Помощник блокировки сайта.
 * @author Гурус
 * @package IcEngine
 *
 */
class Helper_Site_Block
{

	/**
	 * @desc Поле в $_SESSION для идентификации пользователя.
	 * @var string
	 */
	const SESSION_FIELD = 'Helper_Site_Block_Auth';

	/**
	 * @desc Конфиг
	 * @var array
	 */
	public static $config = array (
		'auth_type'	=> 'Html',
		'enable'	=> false,
		'pathes'	=> array (
			'full'	=> array (
				'pattern'	=> '/*',
				'login'		=> 'admin',
				'password'	=> 'admin'
			)
		),
		'blank'		 	=> 'View/sys/site_closed.php',
		'send_headers'	=> true,
		'basicRealm'	=> 'Admin Zone'
	);

	/**
	 * @desc Авторизация через HTML страницу.
	 * @param string $login Логин.
	 * @param string $password Пароль.
	 * @return boolean Успешность авторизации.
	 */
	public function authWithHtml ($login, $password)
	{
		$key = md5 ($login . '@' . $password);

		if (!session_id ())
		{
			session_start ();
		}

		if (
			isset ($_SESSION [self::SESSION_FIELD]) &&
			$_SESSION [self::SESSION_FIELD] == $key
		)
		{
			return true;
		}

		if (
			isset ($_POST ['Html_Auth'], $_POST ['login'], $_POST ['password']) &&
			$_POST ['login'] == $login && $_POST ['password'] == $password
		)
		{
			$_SESSION [self::SESSION_FIELD] = $key;
			return true;
		}

		echo '
<html>
	<head>
		<title>' . self::$config ['basicRealm'] . '</title>
	</head>
	<body>
		<form method="post">
			<table style="margin: 200px auto;">
				<tr>
					<td>login</td>
					<td><input type="text" name="login" /></td>
				</tr>
				<tr>
					<td>password</td>
					<td><input type="password" name="password" /></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="Html_Auth" value="Enter" /></td>
				</tr>
			</table>
		</form>
	</body>
</html>';

		die ();
		return false;
	}

	/**
	 * @desc Авторизация средствами HTTP. Не работает, если php установлен
	 * не как модуль апача.
	 * @param string $login Логин.
	 * @param string $password Пароль.
	 * @return boolean
	 */
	public function authWithHttp ($login, $password)
	{
		// PHP-CGI auth fix:
		if (isset ($_SERVER ['HTTP_AUTHORIZATION']))
		{
			$auth_params = explode (":", base64_decode (substr ($_SERVER ['HTTP_AUTHORIZATION'], 6)));
			$_SERVER['PHP_AUTH_USER'] = $auth_params [0];
			unset ($auth_params [0]);
			$_SERVER['PHP_AUTH_PW'] = implode ('', $auth_params);
		}

		if (
			array_key_exists ('PHP_AUTH_USER', $_SERVER) &&
			array_key_exists ('PHP_AUTH_PW', $_SERVER) &&
			$_SERVER ['PHP_AUTH_USER'] == $login &&
			$_SERVER ['PHP_AUTH_PW'] == $password
		)
		{
			return true;
		}

		if (self::$config ['send_headers'])
		{
			header (
				'WWW-Authenticate: Basic realm="' .
				self::$config ['basicRealm'] . '"');
			header ('HTTP/1.0 401 Unauthorized');
		}

		return false;
	}

	/**
	 * @desc Блокирует сайт в соответсвии с настройками.
	 * @return boolean
	 */
	public static function execute ()
	{
		self::$config = Config_Manager::get (__CLASS__)->merge (self::$config);

		if (!self::$config ['enable'])
		{
			return true;
		}

		foreach (self::$config ['pathes'] as $path)
		{
			if (fnmatch ($path ['pattern'], Request::uri ()))
			{
				$auth_func = 'authWith' . self::$config ['auth_type'];
				if (
					call_user_func (
						array (__CLASS__, $auth_func),
						$path ['login'], $path ['password']
					)
				)
				{
					return true;
				}

				if (self::$config ['blank'])
				{
					include self::$config ['blank'];
				}
				return false;
			}
		}

		return true;
	}

}