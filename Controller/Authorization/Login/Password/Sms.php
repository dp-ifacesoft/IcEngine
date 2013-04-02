<?php

/**
 * Контроллер для авторизации по емейлу, паролю и смс.
 * Предназначен для авторизации контентов в админке, поэтому
 * сверяет данные из БД с данными из файла конфига.
 *
 * @author goorus, morph
 */
class Controller_Authorization_Login_Password_Sms extends Controller_Abstract
{
	/**
	 * @param Аттрибут с кодом, высланным в СМС
	 * @var string
	 */
	const SMS_CODE_ATTR = 'smsAuthCode';

	/**
	 * @param Аттрибут - количество отправленных СМС
	 * @var string
	 */
	const SMS_SEND_COUNTER_ATTR = 'smsAuthSendCount';

	/**
	 * @param Аттрибут со временем последней отправки кода
	 * @var string
	 */
	const SMS_SEND_TIME_ATTR = 'smsAuthSendTime';

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $config = array(
		// Лимит смс в 1 минуту
		'sms_send_limit_1m'			=> 60,

		// Лимит смс на 10 минут
		'sms_send_limit_10m'		=> 190
	);

	/**
	 * Просто форма авторизации
	 */
	public function index()
	{
        IcEngine::getTask()->setTemplate('Controller/Front/login');
        IcEngine::getTask()->getOutput()->send(array(
            'helperViewResource'    => $this->getService('helperViewResource')
        ));
	}

	/**
	 * Авторизация
	 *
     * @param string $name Емейл пользователя
	 * @param string $pass Пароль
	 * @param string $code Код активации из СМС
	 */
	public function login($name, $pass, $a_id, $code, $href)
	{
		$this->task->setTemplate(null);
		$modelManager = $this->getService('modelManager');
		if (!$a_id && $code) {
            $user = $modelManager->byOptions(
                'User',
                '::Active',
                array(
                    'name'  => 'Login',
                    'value' => $name
                ),
                array(
                    'name'  => 'Password',
                    'value' => $pass
                )
            );
            if (!$user) {
                return;
            }
            $activation = $modelManager->byOptions(
                'Activation',
                array(
                    'name'  => '::User',
                    'id'    => $user->key()
                ),
                array(
                    'name'  => '::Code',
                    'code'  => $code
                )
            );
            if ($activation) {
				$a_id = $activation->key();
			}
        }
        if (!$a_id || !$code) {
            return $this->replaceAction($this, 'sendSmsCode');
        }
        $authozization = $modelManager->byOptions(
            'Authorization',
            array(
                'name'  => '::Name',
                'value' => 'Login_Password_Sms'
            )
        );
		$user = $authozization->authorize(array(
			'login'				=> $name,
			'password'			=> $pass,
			'activation_id'		=> $a_id,
			'activation_code'	=> $code
		));
		if (!is_object($user)) {
			// Пользователя не существует
			return $this->sendError(
				'authorization error: ' . $user,
				$user ? $user : __METHOD__,
				$user ? null : '/passwordIncorrect'
			);
		}
		// Сбрасываем счетчик СМС.
		$user->attr(array(
			self::SMS_SEND_COUNTER_ATTR	=> 0,
			self::SMS_CODE_ATTR			=> ''
		));
        if ($user->hasRole('client-manager')) {
            $href = '/admin/manager/';
        }
		$redirect = $this->getService('helperUri')->validRedirect($href);
		$this->output->send(array(
            'data'  => array(
                'redirect'  => $redirect
            )
        ));
	}

	/**
	 * Отправка СМС кода
	 */
	public function sendSmsCode($provider, $name, $pass, $send)
	{
        $modelManager = $this->getService('modelManager');
        $this->task->setTemplate(null);
        if (!$name || !$pass) {
            return $this->sendError('empty login or password');
        }
		$user = $modelManager->byOptions(
			'User',
			array(
				'name'	=> 'Login',
				'value'	=> $name
			),
			array(
				'name'	=> 'Password',
				'value'	=> $pass
			)
		);
		if (!$user) {
			$user = $modelManager->byOptions(
				'User',
				array(
					'name'	=> 'Login',
					'value'	=> $name
				),
				array(
					'name'	=> 'Password',
					'value'	=> $pass
				)
			);
		}
		if (!$user) {
			return $this->sendError(
				'password incorrect',
				'Data_Validator_Authorization_Password/invalid'
			);
		}
		if (!$user->active) {
			return $this->sendError(
				'user unactive',
				'Data_Validator_Authorization_User/unactive'
			);
		}
		if (!$user->phone) {
			return $this->sendError('noPhone');
		}
		$count = $user->attr(self::SMS_SEND_COUNTER_ATTR);
		$time = $this->getService('helperDate')->toUnix();
		$lastTime = $user->attr(self::SMS_SEND_TIME_ATTR);
		$deltaTime = $this->getService('helperDate')->secondsBetween(
            $lastTime
        );
        $config = $this->config();
        if ($count >= $config->sms_send_limit_1m && $deltaTime < 60) {
            return $this->sendError('smsLimit');
        }
        if ($count >= $config->sms_send_limit_10m && $deltaTime < 600) {
            return $this->sendError('smsLimit');
        }
        $authozization = $modelManager->byOptions(
            'Authorization',
            array(
                'name'  => '::Name',
                'value' => 'Login_Password_Sms'
            )
        );
		$activation = $authozization->sendActivationSms(array(
			'login'		=> $name,
			'password'	=> $pass,
			'phone'		=> $user->phone,
			'user'		=> $user,
			'provider'	=> $provider,
			'send'		=> $send
		));
		if (!is_object($activation)) {
			return $this->sendError(
				'send activation code fail (' . (string) $activation . ')',
				$activation ? $activation : 'accessDenied'
			);
		}
		$user->attr(array(
			self::SMS_SEND_TIME_ATTR    => $time,
			self::SMS_SEND_COUNTER_ATTR	=> $count + 1
		));
		$this->output->send(array(
			'activation'	=> $activation,
			'time'			=> $time,
			'data'			=> array(
				'activation_id'		=> $activation->key()
			)
		));
	}
}