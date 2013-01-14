<?php

/**
 *
 * @desc Клиент для Icq
 * @author Илья
 * @package IcEngine
 */
class Client_Icq extends Client_Abstract
{

	/**
	 *
	 * @desc Экзмепляр класса ICQClient
	 * @var ICQClient
	 */
	private $_instance;

	/**
	 *
	 * Если класс не проинициализирован, то проинициализивать
	 * @param null|Config_Array $config
	 * Конфиг для подключения к Icq
	 * @throws Zend_Exception
	 */
	public function __construct ($config = null)
	{
		Loader::requireOnce ('ICQClient.php', 'includes');
		if (!class_exists ('WebIcqPro'))
		{
			throw new Zend_Exception ('Class "ICQClient" not exists');
		}

		$this->_provider = new WebIcqPro;
		$this->_config = $config;
	}

	/**
	 *
	 * @desc Законектится
	 * @param Config_Array $config
	 * @return boolean
	 */
	public function connect ($config)
	{
		return $this->_provider
			->connect (
				$config ['login'],
				$config ['password']
			);
	}

	/**
	 *
	 * @desc Был ли коннект
	 * @return boolean
	 */
	public function connected ()
	{
		return $this->_provider->isConnected ();
	}

	/**
	 *
	 * @desc Получить провайдера icq
	 * @return ICQClient
	 */
	public function provider ()
	{
		return $this->_provider;
	}


	/**
	 *
	 * @desc Отравить сообщение
	 * @param Client_Icq_Reciever $reciever
	 * @param string $message
	 * @return boolean
	 */
	public function send (Client_Icq_Reciever $reciever, $message)
	{
		if (!$this->connected ())
		{
			$this->connect ($this->_config);
		}

		return $this->_provider
			->sendMessage (
				$reciever->icq,
				$message
			);
	}
}