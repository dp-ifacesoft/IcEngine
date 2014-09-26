<?php

/**
 * Класс для работы с шифрованием RSA
 * W - wrapper
 *
 * @author neon
 */
class Crypt_RSAW extends Crypt_Abstract
{
	private $keyPath = 'Ice/Var/';
	private $_instance = null;
	private $publicFile = 'public.txt';
	private $privateFile = 'private.txt';

	/**
	 * @inheritdoc
	 */
	public function __construct()
	{
		$this->keyPath = IcEngine::root() . $this->keyPath;
		$this->publicFile = $this->keyPath . $this->publicFile;
		$this->privateFile = $this->keyPath . $this->privateFile;
        IcEngine::getLoader()->load('RSA2', 'Vendor');
		$this->_instance = new Crypt_RSA();
		$this->checkError($this->_instance);
		$this->loadKeys();
	}

	/**
	 * Проверка ошибок
	 *
	 * @param mixed $object
	 */
	private function checkError($object)
	{
		if ($object->isError()) {
			/** @var Exception $error */
            $error = $object->getLastError();
			Debug::log('ERROR:' . $error->getMessage() . "\n" . $error->getTraceAsString());
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 * @param string $key
	 */
	public function decode($input, $key = null)
	{
		$out = $this->_instance->decrypt($input);
		$this->checkError($this->_instance);
		return $out;
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 * @param string $key
	 */
	public function encode($input, $key = null)
	{
		$out = $this->_instance->encrypt($input);
		$this->checkError($this->_instance);
		return $out;
	}

	/**
	 * Генерирует пару ключей public/private
	 *
	 * @param int $length
	 */
	public function genKeys($length = 1280)
	{
		$keyPair = new Crypt_RSA_KeyPair($length);
		$this->checkError($keyPair);
		$publicKey = $keyPair->getPublicKey();
		$privateKey = $keyPair->getPrivateKey();
		file_put_contents(
			$this->keyPath . 'public.txt',
			$publicKey->toString()
		);
		file_put_contents(
			$this->keyPath . 'private.txt',
			$privateKey->toString()
		);
	}

	/**
	 * Загрузить ключи
	 */
	public function loadKeys()
	{
		if (!$this->issetKeys()) {
			$this->genKeys();
		}
		$publicKeyString = file_get_contents($this->publicFile);
		$privateKeyString = file_get_contents($this->privateFile);
		$publicKey = Crypt_RSA_Key::fromString($publicKeyString);
		$this->checkError($publicKey);
		$privateKey = Crypt_RSA_Key::fromString($privateKeyString);
		$this->checkError($privateKey);
		$this->_instance->setParams(array(
			'enc_key' => $publicKey,
			'dec_key' => $privateKey
		));
		$this->checkError($this->_instance);
	}

	/**
	 * Проверяет существуют ли ключи
	 *
	 * @return bool
	 */
	public function issetKeys()
	{
		if (!file_exists ($this->publicFile) ||
			!file_exists ($this->privateFile)) {
			return false;
		}
		return true;
	}
}