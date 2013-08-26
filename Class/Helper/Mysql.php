<?php

/**
 * Хелпер для работы с mysql
 *
 * @author morph, goorus
 * @Service("helperMysql")
 */
class Helper_Mysql extends Helper_Abstract
{
	const SQL_ESCAPE = '`';
	const SQL_QUOTE	 = '"';
	const SQL_WILDCARD = '*';

    /**
     * Необходимый драйвер данных
     * 
     * @return Data_Driver_Mysqli
     */
    protected function driver()
    {
        $sourceConfig = $this->getService('dds')->getDataSource()->getConfig();
        $config = $sourceConfig['options'];
        return $this->getService('dataDriverManager')->get('Mysqli', $config);
    }
    
	/**
	 * Обособляет название mysql терма, если в этом есть необходимость.
	 *
	 *	Функция вернет исходную строку, если в ней присутствуют спец. символы
	 * (точки, скобки, кавычки, знаки мат. операций и т.п.)
	 * @param string $value Название терма.
	 * @return string Резултат обособления.
	 */
	public function escape ($value)
	{
 		if (
			strpos($value, self::SQL_WILDCARD) === false &&
			strpos($value, '(') === false &&
			strpos($value, ' ') === false &&
			strpos($value, '.') === false &&
			strpos($value, '<') === false &&
			strpos($value, '>') === false &&
			strpos($value, '`') === false
		)
		{
			return self::SQL_ESCAPE .
				addslashes(iconv('UTF-8', 'UTF-8//IGNORE', $value)) .
				self::SQL_ESCAPE;
		}
		return $value;
	}

    /**
     * Получить поля таблицы
     * 
     * @param string $tableName
     * @return array
     */
    public function fields($tableName)
    {
        $query = $this->getService('query')
            ->show('FULL COLUMNS')
            ->from($tableName);
        return $this->driver()->execute($query)->asTable();
    }
    
    /**
     * Получить индексы таблицы
     * 
     * @param string $tableName
     * @return array
     */
    public function indexes($tableName)
    {
        $query = $this->getService('query')
            ->show('INDEXES')
            ->from($tableName);
        return $this->driver()->execute($query)->asTable();
    }
    
	/**
	 * Заключает выражение в кавычки
	 *
     * @param mixed $value
	 * @return string
	 */
	public function quote($value)
	{
		if (is_array($value)) {
			debug_print_backtrace();
			die ();
		}
		return self::SQL_QUOTE .
			addslashes(iconv('UTF-8', 'UTF-8//IGNORE', stripslashes ($value))) .
			self::SQL_QUOTE;
	}
    
    /**
     * Получить информацию о таблице
     * 
     * @param string $tableName
     * @return array
     */
    public function table($tableName)
    {
        $query = $this->getService('query')
            ->show('TABLE STATUS')
            ->resetPart(Query::FROM)
            ->where('Name', trim($tableName, '`'));
        return $this->driver()->execute($query)->asRow();
    }
}