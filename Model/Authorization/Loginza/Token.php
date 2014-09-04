<?php

/**
 * Модель, содержащая информацию по ключу в логинзе.
 *
 * @author goorus, morph
 * @Service("authorizationLoginzaToken")
 */
class Authorization_Loginza_Token extends Model
{
	/**
	 * @inheritdoc
	 */
	protected static $config = array (
		// Адрес логинзы, где хранится результат авторизации
		'loginzaUrl'    => 'http://loginza.ru/api/authinfo?token={$token}'
	);

	/**
	 * Текущие данные (полученные в этом процессе).
	 *
     * @var Authorization_Loginza_Token
	 */
	protected static $current;

	/**
	 * Перенаправление пользователя с ключом результата авторизации.
	 *
     * @param mixed $token Ключ сессии в логинзе. Если null, ключ будет
	 * взят из GET параметров запроса.
	 * @return Authorization_Loginza_Token Данные, полученные от логинзы.
	 */
	public function tokenData($token)
	{
		if (!$token) {
			return null;
		}
        if (self::$current && self::$current->token == $token) {
            return self::$current;
        }
		self::$current = new self(array(
			'time'		=> $this->getService('helperDate')->toUnix(),
			'token'		=> $token,
		));
		$url = str_replace(
			'{$token}', $token, self::$current->config()->loginzaUrl
		);
		$result = file_get_contents($url);
		$data = json_decode($result, true);
		if (isset($data['error_type'])) {
			self::$current = null;
			return null;
		}
		// Успешная авторизация
		self::$current->data('result', $data);
		self::$current->set(array (
			'result'		=> $result,
			'email'		=> isset($data['email']) ? $data['email'] : '',
			'identity'	=> $data['identity'],
			'provider'	=> isset($data['provider']) ? $data['provider'] : ''
		));
		return self::$current;
	}
}