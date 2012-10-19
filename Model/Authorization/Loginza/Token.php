<?php
/**
 *
 * @desc Модель, содержащая информацию по ключу в логинзе.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Loginza_Token extends Model
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		// Адрес логинзы, где хранится результат авторизации
		'loginza_url'				=> 'http://loginza.ru/api/authinfo?token={$token}'
	);

	/**
	 * @desc Текущие данные (полученные в этом процессе).
	 * @var Authorization_Loginza_Token
	 */
	protected static $_current;

	/**
	 * @desc Перенаправление пользователя с ключом результата авторизации.
	 * @param mixed $token Ключ сессии в логинзе. Если null, ключ будет
	 * взят из GET параметров запроса.
	 * @return Authorization_Loginza_Token Данные, полученные от логинзы.
	 */
	public static function tokenData ($token = null)
	{
		if (is_null ($token))
		{
			if (self::$_current)
			{
				return self::$_current;
			}
			$token = Request::get ('lztoken');
			if (!$token)
			{
				$token = Request::post ('token');
			}
		}

		if (!$token)
		{
			return null;
		}

		self::$_current = new self (array (
			'time'		=> Helper_Date::toUnix (),
			'token'		=> $token,
		));

		$url = str_replace (
			'{$token}',
			$token,
			self::$_current->config ()->loginza_url
		);

		$result = file_get_contents ($url);
		$data = json_decode ($result, true);

		if (isset ($data ['error_type']))
		{
			// Не удалось авторизоваться
//			$this->_output->send (array (
//				'error'	=> $data ['error_type'],
//				'data'	=> array (
//					'error_type'	=> $data ['error_type'],
//					'error_message'	=> $data ['error_message']
//				)
//			));
			self::$_current = null;
			return null;
		}

		// Успешная авторизация
		self::$_current->data ('data', $data);

		self::$_current->set (array (
			'data'		=> $result,
			'email'		=>
				isset ($data ['email'])
				? $data ['email']
				: '',
			'identity'	=> $data ['identity'],
			'provider'	=>
				isset ($data ['provider'])
				? $data ['provider']
				: ''
		));

		return self::$_current;
	}

	/**
	 * @desc Возвращает имя пользователя.
	 * Возвращает первое из возможных значения: никнейм, имя, часть
	 * емейла до "@".
	 * @return string
	 */
	public function extractName ()
	{
		$data = $this->data ('data');

		// Никнейм
		if (isset ($data ['nickname']) && $data ['nickname'])
		{
			return
				is_array ($data ['nickname']) ?
					reset ($data ['nickname']) :
					$data ['nickname'];
		}

		// Имя
		if (isset ($data ['name']) && $data ['name'])
		{
			return
				is_array ($data ['name']) ?
					reset ($data ['name']) :
					$data ['name'];
		}

		// емейл
		if (isset ($data ['email']) && $data ['email'])
		{
			return Helper_Email::extractName (
				is_array ($data ['email']) ?
					reset ($data ['email']) :
					$data ['email']
			);
		}

		return '';
	}

}