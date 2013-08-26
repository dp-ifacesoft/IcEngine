<?php

/**
 * DDS Default Data Source
 *
 * Easy way to call querys to DB like
 * DDS::execute($query)
 *
 * @author goorus, morph
 * @Service("dds")
 */
class DDS
{
	/**
	 * Источник данных по умолчанию
	 *
     * @var Data_Source_Abstract
	 */
	protected $source;

	/**
	 * Выполняет запрос и возвращает текущний источник
	 *
     * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Опции
     * @param boolean $auto Пытаться ли автоматически получить источник данных
	 * @return Data_Source источник данных
	 */
	public function execute(Query_Abstract $query, $options = null,
        $auto = true)
	{
        $dataSource = $this->source;
        if ($auto) {
            $from = null;
            $fromParts = $query->getPart(Query::FROM);
			$fromPartTruncate = $query->getPart(Query::TRUNCATE_TABLE);
			$fromPartUpdate = $query->getPart(Query::UPDATE);
            $fromPartInsert = $query->getPart(Query::INSERT);
			if ($fromParts){
				$fromPart = reset($fromParts);
				$from = $fromPart[Query::TABLE];
			} elseif ($fromPartTruncate) {
				$from = reset($fromPartTruncate);
			} elseif ($fromPartUpdate) {
				$from = $fromPartUpdate;
			} elseif ($fromPartInsert) {
                $from = $fromPartInsert;
            }
            if ($from) {
                $scheme = IcEngine::serviceLocator()->getService('modelScheme');
                $dataSource = $scheme->dataSource($from);
            }
        }
		return $dataSource->execute($query, $options);
	}

	/**
	 * Возвращает текущий источник по умолчанию
     *
	 * @return Data_Source_Abstract
	 */
	public function getDataSource()
	{
		return $this->source;
	}

	/**
	 * Изменить источник данных по умолчанию
     *
	 * @param Data_Source $source
	 */
	public function setDataSource(Data_Source $source)
	{
		$this->source = $source;
	}
}