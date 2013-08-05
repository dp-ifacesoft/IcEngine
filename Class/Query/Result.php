<?php

/**
 * Результат работы запроса
 *
 * @author goorus, morph
 */
class Query_Result
{
	/**
	 * Результат.
	 *
     * @var array
	 */
	private $result;

	/**
	 * Создает и возвращает результат запроса
	 *
     * @param array $result Результаты запроса.
	 */
	public function __construct($result)
	{
		$this->result = $result;
	}

	/**
	 * Возвращает одну колонку результата.
	 *
     * @param string|null $name [optional]
	 * 		Название колонки.
	 * 		Если не указано, будет возвращен массив, содержащий
	 * 		первую колонку.
	 * @return array
	 */
	public function asColumn($name = null)
	{
		$result = array();
		if ($name) {
			foreach ($this->result['result'] as $row) {
				$result[] = $row[$name];
			}
		} else {
			foreach ($this->result['result'] as $row) {
				$result[] = reset($row);
			}
		}
		return $result;
	}

	/**
	 * Результат запроса - единственная запись таблицы.
	 *
     * @return array|null
	 */
	public function asRow()
	{
		if (empty($this->result['result'])) {
			return null;
		}
		// результат - массив
		if (is_array($this->result['result'])) {
			$result = reset($this->result['result']);
			return is_array($result) ? $result : null;
		}
		// результат - объект
		foreach ($this->result['result'] as $result)
		{
			return $result;
		}
	}

	/**
	 * Результат запроса - таблица записей.
	 *
     * @param string $key
	 * 		Поле которое будет использоваться в качестве ключа
	 * 		при формировании массива строк. Если не указано,
	 * 		будут использованы номера в порядке выбора.
	 * @return array
	 */
	public function asTable($key = null)
	{
		if (empty($this->result['result'])) {
			return array();
		}
        if ($key) {
            $result = array();
            foreach ($this->result['result'] as $row) {
                $result[$row[$key]] = $row;
            }
            return $result;
        }
        return $this->result['result'];
	}

	/**
	 * Результат запроса - единственное значение.
	 *
     * @return mixed
	 */
	public function asValue()
	{
        if (empty($this->result['result'])) {
            return null;
        }
		reset($this->result['result'][0]);
		return current($this->result['result'][0]);
	}

	/**
	 * Возвращает общее количество подходящих под условие строк.
	 *
     * @return integer
	 */
	public function foundRows()
	{
	    return $this->result['foundRows'];
	}

	/**
	 * Значение последнего добавленого автоинкрементного ключа.
	 *
     * @return integer
	 */
	public function insertId()
	{
		return $this->result['insertKey'];
	}

	/**
	 * Проверяет является ли результат запроса нулевым сетом
     *
	 * @return boolean
	 */
	public function isNull()
	{
		return !empty($this->result['is_null']);
	}

    /**
     * Получить источник данных результата
     *
	 * @return Data_Source_Abstract
	 */
	public function getSource()
	{
		return $this->result['source'];
	}

    /**
     * Получить запрос результата
     *
     * @return Query_Abstract
     */
    public function getQuery()
    {
        return $this->result['query'];
    }

    /**
     * Получить количество рядов, полученных последним запросом
     *
     * @return integer
     */
    public function numRows()
    {
        return isset($this->result['numRows']) ? $this->result['numRows'] : 0;
    }

    /**
     * Получить схему результата
     *
	 * @return mixed
	 */
	public function result()
	{
		return $this->result['result'];
	}

    /**
     * Изменить результаты выборки
     *
     * @param array $result
     */
    public function setResult($result)
    {
        $this->result['result'] = $result;
    }

	/**
	 * Количество затронутых запросом записей.
	 * Количество удаленных, измененных, добавленных или выбранных записей.
	 *
     * @return integer
	 */
	public function touchedRows()
	{
		return $this->result['touchedRows'];
	}
}