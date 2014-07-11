<?php

/**
 * Хелпер транслятора Mysql
 * 
 * @author morph
 * @Service("helperQueryTranslatorMysql")
 */
class Helper_Query_Translator_Mysql extends Helper_Abstract
{
    /**
	 * @see Helper_Mysql::escape()
	 */
	public function escape($value)
	{
        $locator = IcEngine::serviceLocator();
        $helperMysql = $locator->getService('helperMysql');
        return $helperMysql->escape($value);
	}
    
    /**
     * Экранировать колонку по частям (отдельно таблицу если есть, 
     * отдельно поле)
     * 
     * @param string $column
     * @return string
     */
    public function escapePartial($column)
    {
        if (strpos($column, '.') !== false) {
            $columnParts = explode('.', $column);
            $callable = array($this, 'escape');
            $mappedColumnParts = array_map($callable, $columnParts);
            $column = implode('.', $mappedColumnParts);
        } elseif (!$this->isExpression($column) && 
            !$this->isEscaped($column)) {
            $column = $this->escape($column);
        } 
        return $column;
    }
    
    /**
     * Является ли выражение уже экранированным
     * 
     * @param string $value
     * @return string
     */
    public function isEscaped($value)
    {
        return strpos($value, '`') !== false;
    }

    /**
     * Проверить является ли строка выражением
     * 
     * @param string $value
     * @return string
     */
    public function isExpression($value)
    {
        return 
            strpos($value, '\'') === false && strpos($value, '"') === false &&
            (strpos($value, '(') !== false || strpos($value, ')') !== false);
    }
    
	/**
	 * @see Helper_Mysql::quote()
	 */
	public function quote($value)
	{
        $locator = IcEngine::serviceLocator();
        $helperMysql = $locator->getService('helperMysql');
		return $helperMysql->quote($value);
	}
}