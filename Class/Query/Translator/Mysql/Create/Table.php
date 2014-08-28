<?php

/**
 * Транслятор запросов типа create table для mysql
 *
 * @author morph, goorus
 */
class Query_Translator_Mysql_Create_Table extends
    Query_Translator_Mysql_Alter_Table
{
	const SQL_CREATE_TABLE		= 'CREATE TABLE';
	const SQL_IF_NOT_EXISTS		= 'IF NOT EXISTS';
	const SQL_DEFAULT_CHARSET	= 'DEFAULT CHARSET';
	const SQL_ENGINE            = 'ENGINE';
	const SQL_COMMENT			= 'COMMENT';
	const DEFAULT_CHARSET		= 'utf8';
	const DEFAULT_ENGINE        = 'InnoDB';

    /**
	 * Рендер части запроса create table
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	public function doRenderCreateTable(Query_Abstract $query)
	{
        $modelScheme = $this->modelScheme();
		$createTable = $query->part(Query::CREATE_TABLE);
		$name = $createTable[Query::NAME];
        $table = $modelScheme->table($name);
        $helper = $this->helper();
		$sql = self::SQL_CREATE_TABLE . ' ' . self::SQL_IF_NOT_EXISTS . ' ' .
			$helper->escape ($table) . '(';
		$fields = $this->renderFields($query) ;
		return $sql . PHP_EOL .
			($fields ? $fields . ',' . PHP_EOL : '') .
			$this->renderIndexes($query) . PHP_EOL . ') ' .
			$this->renderEngine($query) . ' ' .
			$this->renderCharset($query) . ' ' .
			$this->renderComment($query);
	}

	/**
	 * Рендерит часть запроса default charset
	 *
     * @param Query_Abstract $query
	 */
	protected function renderCharset(Query_Abstract $query)
	{
		$createTable = $query->part(Query::CREATE_TABLE);
		$charset = !empty($createTable[Query::DEFAULT_CHARSET])
			? $createTable[Query::DEFAULT_CHARSET]
			: self::DEFAULT_CHARSET;
		return self::SQL_DEFAULT_CHARSET . '=' . $charset;
	}

	/**
	 * Рендерит часть запроса comment
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderComment(Query_Abstract $query)
	{
		$createTable = $query->part(Query::CREATE_TABLE);
        if (empty($createTable[Query::COMMENT])) {
            return;
        }
        $comment = $createTable[Query::COMMENT];
        $helper = $this->helper();
		return self::SQL_COMMENT . '=' . $helper->quote($comment);
	}

	/**
	 * Рендерит часть запроса engine
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderEngine(Query_Abstract $query)
	{
		$createTable = $query->part(Query::CREATE_TABLE);
		$engine = !empty($createTable[Query::ENGINE])
			? $createTable[Query::ENGINE]
			: self::DEFAULT_ENGINE;
		return self::SQL_ENGINE. '=' . $engine;
	}

	/**
	 * Рендерит поля
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderFields(Query_Abstract $query)
	{
		$createTable = $query->part(Query::CREATE_TABLE);
        if (empty($createTable[Query::FIELD])) {
            return;
        }
		$fields = $createTable[Query::FIELD];
        foreach ($fields as &$field) {
            $field = "\t" . $this->renderField(
                $field[Query::FIELD],
                $field[Query::ATTR]
            );
        }
        return implode(',' . PHP_EOL, $fields);
	}

	/**
	 * Рендерит игдексы
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderIndexes(Query_Abstract $query)
	{
		$createTable = $query->part(Query::CREATE_TABLE);
        if (empty($createTable[Query::INDEX])) {
            return;
        }
		$indexes = $createTable[Query::INDEX];
        foreach ($indexes as &$index) {
            $index = $this->renderIndex($index[Query::NAME], $index);
        }
		return implode(',' . PHP_EOL, $indexes);
	}
}