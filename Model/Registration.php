<?php
/**
 *
 * @desc Модель регистрации на сайте
 * @author Гурус
 * @package IcEngine
 *
 */
class Registration extends Model
{

	/**
	 * @desc Время окончания регистрации, если она не закончена.
	 * @var string
	 */
	const EMPTY_FINISH_TIME = '2000-01-01';

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * Событие после подтверждения емейла
		 * @var function (Registration)
		 */
		'after_confirm'	=> null,

		/**
		 * Событие после создания регистрации
		 * @var function (Registration, array) boolean
		 */
		'after_create'	=> null,

		/**
		 * Автоактивация (не требует активации по email).
		 * @var boolean
		 */
		'auto_active'	=> false,

		/**
		 * @desc Автосоздание и активация пользователя.
		 * @var boolean
		 */
		'auto_user'		=> true,

		// Шаблон сообщения
		'mail_template'		=> 'user_register',

		// Провайдер для отправки сообщений
		'mail_provider'		=> 'Mimemail',

		/**
		 * Отсылать сообщение.
		 * @var boolean
		 */
		'sendmail'		=> false,

		/**
		 * Ограничение на количество регистраций с одного ИП в день
		 * @var integer
		 */
		'ip_day_limit'	=> 20,

		/**
		 * Поля.
		 * @var array
		 */
		'fields'	=> array (
			'email'		=> array (
				'type'	=> 'string',
				'minLength'	=> 5,
				'maxLength'	=> 40,
				'value'		=> 'input',
				'filters'	=> 'Trim',
				'validators'	=> array (
					'Registration_Email'
				)
			),
			'password'	=> array (
				'type'	=> 'string',
				'minLength'	=> 6,
				'maxLength'	=> 250,
				'value'	=> 'input',
				'validators'	=> array (
					'Registration_Password'
				)
			),
			'ip'	=> array (
				'maxTries'	=> 10,
				'value'		=> array ('Request', 'ip'),
				'validators'	=> array (
					'Registration_Ip_Limit'
				)
			)
		)
	);

	/**
	 * @desc Автоактивация пользователя
	 */
	public function _autoUserActivate ()
	{
		if (!$this->User || !$this->User->key ())
		{
			throw new Zend_Exception ('User unexists.');
		}

		$this->User->update (array (
			'active'	=> 1
		));

		if ($this->config ()->after_confirm)
		{
			call_user_func (
				$this->config ()->after_confirm->__toArray (),
				$this
			);
		}
	}

	/**
	 * @desc Автосоздание пользователя после регистрации.
	 * @param array|Objective $data
	 * @return User
	 */
	public function _autoUserCreate ($data)
	{
		return User::create (array_merge (
			array (
				'email'			=> $data['email'],
				'password'		=> $data['password'],
				'name'			=> $data['name'],
				'surname'		=> $data['surname'],
				'Sex__id'		=> $data['Sex__id'],
				'active'		=> $this->config ()->autoactive
			),
			$data
		));
	}

	/**
	 * @desc Возвращает регистрацию по уникальному коду.
	 * @param string $code
	 * @return Registration|null
	 */
	public static function byCode ($code)
	{
		return Model_Manager::byQuery (
			__CLASS__,
			Query::instance ()
				->where ('code', $code)
		);
	}

	/**
	 * @desc Ссылка на подтверждение регистрации.
	 * @return string
	 */
	public function confirmHref ()
	{
		return '/registration/' . $this->code;
	}

	/**
	 * @desc Создание новой регистрации
	 * @param array $data
	 * @param integer $data ['User__id'] id пользователя
	 * @param string $data ['email'] Email
	 * @return Registration Сохраненная регистрация.
	 */
	public static function create (array $data)
	{

		$registration = new Registration (array (
			'id'			=> 0,
			'User__id'		=> $data ['User__id'],
			'email'			=> $data ['email'],
			'time'			=> Helper_Date::toUnix (),
			'ip'			=> Request::ip (),
			'day'			=> Helper_Date::eraDayNum (),
			'finished'		=> 0,
			'finishTime'	=> self::EMPTY_FINISH_TIME,
			'code'			=>
				isset ($data ['code']) ?
					$data ['code'] :
					self::generateUniqueCode ($data ['User__id'])
		));

		return $registration->save (true);
	}

	/**
	 * @desc Завершение процесса регистрации, активация пользователя.
	 * @return Registration
	 */
	public function finish ()
	{
		$this->update (array (
			'finished'		=> 1,
			'finishTime'	=> date ('Y-m-d H:i:s')
		));

		if ($this->config ()->auto_user)
		{
			$this->_autoUserActivate ();
		}

		return $this;
	}

	/**
	 * @desc Возвращает новый уникальный код для активация по емейл.
	 * @param integer $id Первичный ключ регистрации.
	 * @return string
	 */
	public static function generateUniqueCode ($id)
	{
		return
			chr (rand (ord ('a'), ord ('z'))) . $id . 'a' .
			md5 (time ()) . md5 (rand (12345678, 87654321));
	}

	/**
	 * @desc Регистрация.
	 * @param array|Objective $data
	 * @return Registration
	 */
	public function register ($data)
	{
		$config = $this->config ();

		$this->update (array (
			'User__id'		=> 0,
			'email'			=> $data ['email'],
			'time'			=> Helper_Date::toUnix (),
			'ip'			=> Request::ip (),
			'day'			=> Helper_Date::eraDayNum (),
			'finished'		=> 0,
			'finishTime'	=> self::EMPTY_FINISH_TIME,
			'code'			=> ''
		));

		if ($config ['user'])
		{
			$user = $config ['user'];
			$this->update (array (
				'User__id'	=> $user->id,
				'code'	=> self::generateUniqueCode ($user->id)
			));
		}

		if ($config ['auto_user'])
		{
			if ($data ['user']) {
				$user = $data ['user'];
			}
			else
			{
				$user = $this->_autoUserCreate ($data);
			}

			$this->update (array (
				'User__id'	=> $user->id,
				'code'		=> self::generateUniqueCode ($user->id)
			));
		}

		if ($config ['after_create'])
		{
			if (
				!call_user_func (
					$config ['after_create']->__toArray (),
					$this, $data
				)
			)
			{
				$this->delete ();
				$user->delete ();
				return null;
			};
		}

		if ($this->config ()->sendmail)
		{
			$message = Mail_Message::create (
				$config ['mail_template'],
				$data ['email'],
				$data ['email'],
				array (
					'email'		=> $data ['email'],
					'password'	=> $data ['password'],
					'time'		=> $this->time,
					'code'		=> $this->code,
					'href'		=> $this->confirmHref ()
				),
				$user->id
			);
			$message->send ();
		}

		return $this;
	}

	/**
	 * @desc Отправка сообщения о регистрации
	 * @param array $data Дополнительные данные для сообщения.
	 * @return Mail_Message
	 */
	public function sendMail (array $data = array ())
	{
		$config = $this->config ();
		$message = Mail_Message::create (
			$config ['mail_template'],
			$this->email,
			$this->email,
			array_merge (
				array (
					'email'		=> $this->email,
					'time'		=> $this->time,
					'code'		=> $this->code,
					'href'		=> $this->confirmHref ()
				),
				$data
			),
			$this->User__id,
			$config ['mail_provider']
		);
		return $message->send ();
	}

	/**
	 *
	 * @param Objective $data
	 * 		string ['email'] емейл
	 * 		string ['password'] пароль
	 * @return Registration|array Модель регистрации или ошибка.
	 */
	public static function tryRegister ($data)
	{
		if (is_array ($data))
		{
			$data = new Objective ($data);
		}
		$reg = new Registration ();
		$fields = $reg->config ()->fields;
		Helper_Form::filter ($data, $fields);
		$result = Helper_Form::validate ($data, $fields);

		if (is_array ($result))
		{
			return $result;
		}

		Helper_Form::unsetIngored ($data, $fields);

		$reg = $reg->register ($data);

		return
			$reg
			? $reg
			: array ('unknown' => 'Data_Validator_Registration/unknown');
	}

}