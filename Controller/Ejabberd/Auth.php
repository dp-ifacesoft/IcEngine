<?php
/**
 * 
 * @desc Демон для авторизации в ejabberd через сайт.
 * @author Юрий Шведов
 * 
 * Installation:
 *
 *	- Change it's owner to whichever user is running the server, ie. ejabberd
 *	  $ chown ejabberd:ejabberd /var/lib/ejabberd/joomla-login
 *
 * 	- Change the access mode so it is readable only to the user ejabberd and has exec
 *	  $ chmod 700 /var/lib/ejabberd/joomla-login
 *
 *	- Edit your ejabberd.cfg file, comment out your auth_method and add:
 *	  {auth_method, external}.
 *	  {extauth_program, "php ics.php secret:localhost Ejabberd_Auth daemon"}.
 *
 *	- Restart your ejabberd service, you should be able to login with your site auth info
 *
 * Other hints:
 *	- if your users have a space or a @ in their username, they'll run into trouble
 *	  registering with any client so they should be instructed to replace these chars
 *	  " " (space) is replaced with "%20"
 *	  "@" is replaced with "(a)"
 *
 *	- if your users have special chars and you're not using UTF-8, set
 *	  config ['charset'] below to match your Joomla encoding
 *
 * 
 */
class Controller_Ejabberd_Auth extends Controller_Abstract
{
	
	/**
	 * @desc Ведение логов
	 * @var boolean
	 */
	protected $_loggin = false;
	
	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected $_config = array (
		/**
		 * @desc Кодировка базы
		 * @var string
		 */
		'charset'	=> 'UTF-8',
		/**
		 * @desc Файл для записи логов
		 * @var string
		 */
		'logging'	=> false,
		/**
		 * @desc Разрешить использовать в качестве пароля
		 * идентификатор сессии.
		 * @var boolean
		 */
		'session_auth'	=> true
	);
	
	public function __construct ()
	{
		$this->_logging = $this->config ()->logging;
	}
	
	/**
	 * @desc Авторизация пользователя
	 * @param array $commands
	 */
	public function _cmdAuth (array $commands)
	{
		// provjeravamo autentifikaciju korisnika
		if (sizeof ($commands) != 4)
		{
			$this->_log ("[exAuth] invalid auth command, data missing");
			fwrite (STDOUT, pack ("nn", 2, 0));
			return;
		}

		// ovdje provjeri prijavu
		$sUser = str_replace (
			array ("%20", "(a)"),
			array (" ", "@"),
			$commands [1]
		);
		$this->_log ("[debug] doing auth for ". $sUser);
		
		$jid = $sUser . '@' . $commands [2];

		$query = 
			Query::instance ()
				->select ('id, password')
				->from ('User')
				->where ('jid', $jid);

		$this->_log ("[debug] using query " . $query->translate ('Mysql'));

		$user = Model_Scheme::dataSource ('User')
			->execute ($query)
				->getResult ()->asRow ();

		if (!$user)
		{
			$this->_log ("[exAuth] invalid user " . $jid);
			fwrite (STDOUT, pack ("nn", 2, 0));
			return;
		}
		
		// trim for console input
		$auth_key = trim ($commands [3]);

		if ($user ['password'] != $auth_key)
		{
			$this->_log ("[exAuth] invalid password for " . $jid);
			
			if (!$this->_config->session_auth)
			{
				fwrite (STDOUT, pack ("nn", 2, 0));
				return;
			}
			
			// Если не совпал пароль проверяем сессию
			$user_session = Model_Scheme::dataSource ('User_Session')
				->execute (
					Query::instance ()
						->select ('*')
						->from ('User_Session')
						->where ('id', $commands [3])
				)
					->getResult ()->asRow ();

			if (!$user_session)
			{
				$this->_log ("[exAuth] invalid session for " . $jid);
				fwrite (STDOUT, pack ("nn", 2, 0));
				return;
			}
			
			if ($user_session ['User__id'] != $user ['id'])
			{
				$this->_log ("[exAuth] user session id mismatch for " . $jid);
				fwrite (STDOUT, pack ("nn", 2, 0));
				return;
			}
		}
		
		// korisnik OK
		$this->_log ("[exAuth] authentificated user " . $jid);
		fwrite (STDOUT, pack ("nn", 2, 1));
	}
	
	/**
	 * @desc Проверка существования пользователя
	 * @param array $commands
	 */
	public function _cmdIsuser (array $commands)
	{
		// provjeravamo je li korisnik dobar
		if (!isset ($commands [1]))
		{
			$this->_log ("[exAuth] invalid isuser command, no username given");
			fwrite (STDOUT, pack ("nn", 2, 0));
			return;
		}
		
		if (!isset ($commands [2]))
		{
			$this->_log ("[exAuth] invalid isuser command, no host given");
			fwrite (STDOUT, pack ("nn", 2, 0));
			return;
		}
		
		// ovdje provjeri je li korisnik OK
		$sUser = str_replace (
			array ("%20", "(a)"), 
			array (" ", "@"),
			$commands [1]
		);
		
		// trim for console input
		$jid = $sUser . '@' . trim ($commands [2]);
		
		$this->_log ("[debug] checking isuser for $jid");

		$query = Query::instance ()
			->select ('id')
			->from ('User')
			->where ('jid', $jid);

		$this->_log ("[debug] query isuser: " . $query->translate ('Mysql'));
		// т.к. модели будут кэшироваться и постоянно отжирать память,
		// выбираем просто в массив
		$user = Model_Scheme::dataSource ('User')
			->execute ($query)
				->getResult ()->asRow ();

		if ($user)
		{
			// korisnik OK
			$this->_log ("[exAuth] valid user: " . $jid);
			fwrite (STDOUT, pack ("nn", 2, 1));
		}
		else
		{
			// korisnik nije OK
			$this->_log ("[exAuth] invalid user: " . $jid);
			fwrite (STDOUT, pack ("nn", 2, 0));
		}
	}
	
	/**
	 * @desc Смена пароля (не поддерживается)
	 * @param array $commands 
	 */
	public function _cmdSetpass (array $commands)
	{
		// postavljanje zaporke, onemoguceno
		$this->_log ("[exAuth] setpass command disabled");
		fwrite (STDOUT, pack ("nn", 2, 0));
	}
	
	/**
	 * @desc Запись в лог
	 * @param string $text
	 */
	public function _log ($text)
	{
		
//		echo $text;
		
		if (!$this->_logging)
		{
			return;
		}
		
		$f = fopen ($this->_logging, 'a');
		fwrite ($f, date ('m-d H:i:s ') . $text . "\r\n");
		fclose ($f);
	}
	
	/**
	 * @desc Запуск цикла демона авторизации
	 */
	public function daemon ()
	{
		for (;;)
		{
			$this->process ();
		}
	}
	
	/**
	 * @desc Цикл авторизации
	 */
	public function process ()
	{
		$header = fgets (STDIN, 3);
		
		if (!$header)
		{
			return;
		}
		
		$length = unpack ('n', $header);
		$length = $length [1];
		
		if ($length > 0)
		{
			// ovo znaci da smo nesto dobili
			$data = fgets (STDIN, $length + 1);
			
			$cfg_charset = $this->config ()->charset;
			if ($cfg_charset && strtoupper ($cfg_charset) != "UTF-8")
			{
				$data = iconv ("UTF-8", $cfg_charset, $data);
			}
			
			$this->_log ("[debug] received data: " . $data);
			
			$commands = explode (":", $data);
			
			if (is_array ($commands))
			{
				$method = '_cmd' . ucfirst ($commands [0]);
				
				if (method_exists ($this, $method))
				{
					$this->$method ($commands);
				}
				else
				{
					// ako je uhvaceno ista drugo
					$this->_log ("[exAuth] unknown command " . $commands [0]);
					fwrite (STDOUT, pack ("nn", 2, 0));
				}
			}
			else
			{
				$this->_log ("[debug] invalid command string");
				fwrite (STDOUT, pack ("nn", 2, 0));
			}
		}
		
		unset ($header);
		unset ($length);
		unset ($commands);
		
		return true;
	}
	
}
