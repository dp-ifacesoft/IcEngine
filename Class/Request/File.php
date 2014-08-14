<?php

class Request_File
{

	/**
	 * Элемент массива $_FILES
	 * @var array
	 */
	public $file;

	/**
	 * Имя исходного файла
	 * @var string
	 */
	public $name;

	/**
	 * Расширение исходного файла
	 * @var extension
	 */
	public $extension;

	/**
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Размер
	 * @var integer
	 */
	public $size;

	/**
	 * Имя временного файла
	 * @var string
	 */
	public $tmp_name;

	/**
	 * Ошибки загрузки
	 * @var integer
	 */
	public $error;

	/**
	 * Путь к конечному файлу на сервере
	 * @var string
	 */
	public $destination = false;

	/**
	 *
	 * @param array $file элемент из $_FILES
	 */
	public function __construct (array $file)
	{
		$this->file		= $file;
		$this->name		= $file ['name'];
		$this->type		= $file ['type'];
		$this->size		= $file ['size'];
		$this->tmp_name	= $file ['tmp_name'];
		$this->error	= $file ['error'];

		$this->extension = strtolower (substr (strrchr ($this->name, '.'), 1));
	}

	/**
	 * @return boolean
	 */
	public function hasError ()
	{
		return $this->isUploaded () && $this->error != UPLOAD_ERR_OK;
	}

	/**
	 * @return boolean
	 */
	function isUploaded ()
	{
		return $this->error != UPLOAD_ERR_NO_FILE;
	}

	/**
	 * Сохранить файл в $destination
	 * @param string $destination Путь к файлу
	 * @return boolean
	 */
	function save ($destination)
	{
		$this->destination = $destination;
		return copy($this->tmp_name, $destination);
	}

	/**
	 *
	 * @param string $path
	 * @param boolean|string $extension true - сохранить расширение, false - без расширения, str - переданное расширение
	 * @return string|false Имя файла, false - в случае неудачи
	 */
	function saveUniq ($path = 'uploads/', $extension = true)
	{
		$fn = uniqid ();
		if ($extension === true)
		{
			// добавляем расширение исходного файла
			$fn .= '.' . $this->extension;
		}
		elseif ($extension !== false)
		{
			// добавляем переданное расширение
			$fn .= '.' . $extension;
		}

		$this->name = $fn;

		if ($this->save ($path . $fn))
		{
			return $fn;
		}
		else
		{
			return false;
		}
	}

}
