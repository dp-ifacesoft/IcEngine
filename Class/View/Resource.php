<?php

/**
 * Ресурс представления. Информация о файле Js или Css.
 * 
 * @author morph
 */
class View_Resource extends Objective
{
	/**
	 * Время модификации файла
	 * 
     * @var integer
	 */
	protected $filemtime;
	
	/**
	 * Ссылка
     * 
	 * @var string
	 */
	public $href;
	
    /**
	 * Путь до ресурса относительно корня сайта
	 * 
     * @var string
	 */
	public $src;
	
    /**
	 * Тип ресурса
	 * 
     * @var string
	 */
	public $type;
	
	/**
	 * Путь до файла в системе
     * 
	 * @var string
	 */
	public $filePath;
	
	/**
	 * Не использовать сжатие ресурсов при упаковке
     * 
	 * @var boolean
	 */
	public $nopack;
	
	/**
	 * Содержимое ресурса
     * 
	 * @return string
	 */
	public function content()
	{
		$path = $this->filePath;
		return is_file($path) ? file_get_contents($path) : null;
	}
	
	/**
	 * Время модификации файла
     * 
	 * @return integer|null Время модификации. Если файл не существует null.
	 */
	public function filemtime()
	{
		if (is_null($this->filemtime)) {
			$path = $this->filePath;
			$this->filemtime = is_file($path) ? filemtime($path) : 0;
		}
		return $this->filemtime;
	}
}