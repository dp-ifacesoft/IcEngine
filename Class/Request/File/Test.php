<?php
/**
 * 
 * @desc Класс для имитации загрузки файлов.
 * @author Юрий
 * @package IcEngine
 *
 */
class Request_File_Test extends Request_File
{
	
	public $tests = array (
		'test'	=> array (
			'name'		=> 'test.jpg',
			'type'		=> 'jpg',
			'size'		=> 4,
			'tmp_name'	=> '{$engine_path}images/test.jpg',
			'error'		=> false
		)
	);
	
	function __construct ($index = 0)
	{
		$this->tests [$index]['tmp_name'] = str_replace (
			'{$engine_path}',
			IcEngine::path (),
			$this->tests [$index]['tmp_name']
		);
		parent::__construct ($this->tests [$index]);
	}
	
	/**
	 * @return boolean
	 */
	function isUploaded ()
	{
		return true;
	}
	
	/**
	 * Сохранить файл в $destination
	 * @param string $destination Путь к файлу
	 * @return boolean
	 */
	function save ($destination)
	{
		$this->destination = $destination;
		return copy ($this->tmp_name, $destination);
	}
	
}