<?php

/**
 * Хелпер для частей From, Inner_Join, Left_Join и им подобныx
 * 
 * @author morph
 * @service("helperQueryCommandJoin")
 */
class Helper_Query_Command_Join
{
    /**
	 * Добавление джойна таблицы к запросу
	 * 
     * @param string|array $table Название таблицы или
	 * пара (table => alias) или, в случае нескольких алиасов
	 * (table => array (alias1, alias2,...)).
	 * Джойн нескольких таблиц не поддерживается.
	 * @param string $type
	 * @param string $condition optional
	 */
	public function join($table, $type, $condition = null)
	{
		if (is_array($table)) {
			reset($table);
			$aliases = (array) current($table);
			$table = key($table);
		} else {
			$aliases = (array) $table;
		}
        $result = array();
		foreach ($aliases as $alias) {
			$result[$alias] = array(
				Query::TABLE		=> $table,
				Query::WHERE		=> $condition,
				Query::JOIN		=> $type
			);
		}
        return $result;
	}
}