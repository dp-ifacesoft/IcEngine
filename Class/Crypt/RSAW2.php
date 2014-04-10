<?php

/**
 * Класс для работы с шифрованием RSA
 * W - wrapper
 *
 * @author neon
 */
class Crypt_RSAW2 extends Crypt_Abstract
{
	/**
	 * Ключи
	 */
	private $keys = array();

	/**
	 * @inheritdoc
	 */
	public function __construct()
	{
        IcEngine::getLoader()->load('RSA2', 'Vendor');
		$this->_instance = new Crypt_RSA();
		$this->keys = $this->_instance->generate_keys('9990454949', '9990450271');
	}

	private function _prime($n, $s = 2)
	{
		$ret = '';
		for ($i=$s; $i<=$n; $i++) {
			$flag = true;
			for ($k = 2; $k * $k <= $i; $k++) {
				if ($i % $k == 0) {
					$flag = false;
					break;
				}
			}
			if ($flag == true) {
				$ret[] = $i;
			}
		}
		return $ret;
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 * @param string $key
	 */
	public function decode($input, $key = null)
	{
		$out = $this->_instance->decrypt($input, $this->keys[2], $this->keys[0]);
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
		$out = $this->_instance->encrypt($input, $this->keys[1], $this->keys[0], 5);
		return $out;
	}
}