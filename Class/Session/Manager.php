<?php

/**
 * Менеджер для работы с сессиями.
 *
 * @author goorus, morph
 * @Service("sessionManager")
 */
class Session_Manager extends Manager_Abstract
{
	/**
	 * Провайдер данных.
	 *
     * @var Data_Provider_Abstract
	 */
	protected $provider;

	/**
	 * Путь для сохранения сессии
     *
	 * @var string
	 */
	protected $sessionSavePath;

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * @desc Время жизни сессии
		 * @var integer
		 */
		'TTL'			=> 86400,
		/**
		 * @desc Используемый провайдер
		 * @var string
		 */
		'provider'		=> null,
	);

	/**
	 * Close function, this works like a destructor in classes and is
     * executed when the session operation is done.
	 */
	public function close()
	{
		return true;
	}

	/**
	 * The destroy handler, this is executed when a session is destroyed
     * with session_destroy() and takes the session id as its only parameter.
	 */
 	public function destroy($id)
 	{
 		$this->provider->delete($id);
 		return true;
 	}

	/**
	 * The garbage collector, this is executed when the session
     * garbage collector is executed and takes the max session lifetime
     * as its only parameter.
	 */
	public function gc($maxlifetime)
	{

	}

	/**
	 * Инициализация менеджера сессий
	 */
	public function init()
	{
		$config = $this->config();
		if ($config['provider']) {
            $dataProviderManager = $this->getService('dataProviderManager');
            $provider = $dataProviderManager->get($config['provider']);
			$this->initProvider($provider);
		}
	}

	/**
	 * Инициализация провайдера
     *
	 * @param Data_Provider_Abstract $provider
	 */
	public function initProvider(Data_Provider_Abstract $provider)
	{
		$this->provider = $provider;
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * Open function, this works like a constructor in classes and
	 * is executed when the session is being opened. The open function expects
	 * two parameters, where the first is the save path and the second is
	 * the session name.
	 *
     * @param string $save_path
	 * @param string $session_name
	 */
	public function open($savePath, $sessionName)
	{
		$this->sessionSavePath = $savePath;
		return true;
	}

	/**
	 * Read function must return string value always to make save handler
	 * work as expected. Return empty string if there is no data to read.
	 * Return values from other handlers are converted to boolean expression.
	 * TRUE for success, FALSE for failure.
	 */
	public function read($id)
	{
		return (string) $this->provider->get($id);
	}

	/**
	 * Write function that is called when session data is to be saved.
	 * This function expects two parameters: an identifier and the data
	 * associated with it.
	 * Note:
	 * The "write" handler is not executed until after the output stream
	 * is closed. Thus, output from debugging statements in the "write" handler
	 * will never be seen in the browser. If debugging output is necessary,
	 * it is suggested that the debug output be written to a file instead.
	 */
	public function write($id, $data)
	{
		$this->provider->set($id, $data, (int) $this->config()->TTL);
		return true;
	}
}