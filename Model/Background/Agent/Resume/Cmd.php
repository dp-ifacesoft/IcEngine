<?php
/**
 * 
 * @desc Для перезапуска процессов через CMD
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Background_Agent_Resume_Cmd extends Background_Agent_Resume_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $_config = array (
		// Шаблон команды для перезапуска процесса
		'cmd_pattern'		=> 'php ics.php secret Background resume session_id={$session_id} session_key={$session_key}',
		// изменить директорию перед запуском
		'root_directory'	=> false
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
			'{$session_key}'	=> $session->key
		);
		
		$cmd = str_replace (
			array_keys ($values),
			array_values ($values),
			$config ['cmd_pattern']
		);
		
		if ($config ['root_directory'])
		{
			chdir ($config ['root_directory']);
		}
		
		die (popen ($cmd, 'r'));
	}
	
}