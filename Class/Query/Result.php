<?php
/**
 * 
 * @desc Результат работы запроса
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Query_Result 
{
	
	/**
	 * @desc Результат.
	 * @var array
	 */
	private $_result;
	
	/**
	 * @desc Создает и возвращает результат запроса
	 * @param array $result Результаты запроса.
	 */
	public function __construct ($result)
	{
		$this->_result = $result;
	}
	
	/**
	 * @desc Возвращает одну колонку результата.
	 * @param string|null $name [optional]
	 * 		Название колонки.
	 * 		Если не указано, будет возвращен массив, содержащий
	 * 		первую колонку.
	 * @return array
	 */
	public function asColumn ($name = null)
	{
		$result = array ();
		
		if ($name)
		{
			foreach ($this->_result ['result'] as $row)
			{
				$result [] = $row [$name];
			}
		}
		else
		{
			foreach ($this->_result ['result'] as $row)
			{
				$result [] = reset ($row);
			}
		}
		
		return $result;
	}
	
	/**
	 * @desc Результат запроса - единственная запись таблицы.
	 * @return array|null
	 */
	public function asRow ()
	{
		if (empty ($this->_result ['result']))
		{
			return null;
		}
		
		// результат - массив
		if (is_array ($this->_result ['result']))
		{
			$result = reset ($this->_result ['result']);
			return is_array ($result) ? $result : null;
		}
		
		// результат - объект
		foreach ($this->_result ['result'] as $result)
		{
			return $result;
		}
	}
	
	/**
	 * @desc Результат запроса - таблица записей.
	 * @param string $key
	 * 		Поле которое будет использоваться в качестве ключа 
	 * 		при формировании массива строк. Если не указано,
	 * 		будут использованы номера в порядке выбора. 
	 * @return array
	 */
	public function asTable ($key = null)
	{
		if (empty ($this->_result ['result']))
		{
			return array ();
		}
		else
		{
			if ($key)
			{
				$result = array ();
				foreach ($this->_result ['result'] as $row)
				{
					$result [$row [$key]] = $row;
				}
				return $result;
			}
			
			return $this->_result ['result'];
		}
	}
	
	/**
	 * @desc Результат запроса - единственное значение.
	 * @return mixed
	 */
	public function asValue ()
	{
		if (
			!isset ($this->_result ['result'][0]) ||
			empty ($this->_result ['result'][0])
		)
		{
			return null;
		}
		
		reset ($this->_result ['result'][0]);
		return current ($this->_result ['result'][0]);
	}
	
	/**
	 * @desc Возвращает общее количество подходящих под условие строк.
	 * @return integer
	 */
	public function foundRows ()
	{
	    return $this->_result ['foundRows'];
	}
	
	/**
	 * @desc Значение последнего добавленого автоинкрементного ключа.
	 * @return integer
	 */
	public function insertId ()
	{
		return $this->_result ['insertKey'];
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isNull ()
	{
		return 
			isset ($this->_result ['is_null']) &&
			$this->_result ['is_null'];
	}
	
	/**
	 * @desc Количество затронутых запросом записей.
	 * Количество удаленных, измененных, добавленных или выбранных записей.
	 * @return integer
	 */
	public function touchedRows ()
	{
		return $this->_result ['touchedRows'];
	}
	
	/**
	 * @return Data_Source_Abstract
	 */
	public function getSource ()
	{
		return $this->_result ['source'];
	}
	
	/**
	 * @return mixed
	 */
	public function result ()
	{
		return $this->_result ['result'];
	}
	
}