<?php
/**
 *
 * @desc Возобновление работы БГ агента через Http запрос.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Background_Agent_Resume_Http extends Background_Agent_Resume_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $_config = array (
		/**
		 * @desc Адрес для перезапуска процесса
		 * @var string
		 */
		'resume_url'				=> '{$host_name}/Controller/ajax/?controller=Background&action=resume&session_id={$session_id}&key={$session_key}'
	);

	/**
	 * (non-PHPdoc)
	 * @see Background_Agent_Resume_Abstract::resume()
	 */
	public function resume (Background_Agent_Session $session)
	{
		$config = $this->config ();

		$values = array (
			'{$session_id}'		=> $session->id,
			'{$session_key}'	=> $session->key,
			'{$host_name}'		=> $_SERVER ['HTTP_HOST']
		);

		$url = str_replace (
			array_keys ($values),
			array_values ($values),
			$config ['resume_url']
		);

		$url = parse_url ($url);
		
		Helper_File::callUnresultedPage (
			isset ($url ['host']) ? $url ['host'] : $_SERVER ['HTTP_HOST'],
			$url ['path'],
			$url ['query']
		);
	}

}