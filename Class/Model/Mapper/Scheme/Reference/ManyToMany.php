<?php

/**
 * Тип ссылки "многие-ко-многим"
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_ManyToMany extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * Таблицы для связи многие-ко-многим
	 * 
     * @var array
	 */
	protected static $tables;

	/**
	 * Сформировать ключ связи "многие-ко-многим"
	 * 
     * @param string $model_name
	 * @return string
	 */
	public function key($modelName)
	{
		$keyTable = array($modelName, $this->getModel());
		sort($keyTable);
		$key = implode('_', $keyTable);
		$postfix = abs(crc32($keyTable[0]) % crc32($keyTable[1]));
		$key .= $postfix;
		return $key;
	}

	/**
	 * Получить схему
	 * 
	 * @param string $from_table
	 * @param string $to_table
	 * @return Model_Mapper_Scheme_Abstract
	 */
	protected function scheme($fromTable, $toTable)
	{
        $emptyField = array(
            'Int',
            array(
                'Size'  => 11,
                'Auto_Increment',
                'Not_Null'
            )
        );
        $fields = array(
            $fromTable  => $emptyField,
            $toTable    => $emptyField
        );
        $unique = $fromTable . '_' . $toTable;
        $indexes = array(
            'id'    => array('Primary', array('id')),
            $unique => array('Unique', array($fromTable, $toTable))
        );
        return array(
            'fields'    => $fields,
            'indexes'   => $indexes
        );
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
		$key = $this->key($modelName);
		$joinData = $this->getField();
        $fields = $joinData[0];
        $linkTable = isset($joinData[1]) ? $joinData[1] : null;
        if (is_array($fields)) {
            $first = reset($fields);
            $on = $fields['on'];
            $fields = array($first, $on);
        } elseif (!$fields) {
            $fields = array($modelName . '__id', $this->getModel() . '__id');
        }
		$linkFields = $fields;
		sort($linkFields);
		$fromField = $linkFields[0];
		$toField = $linkFields->on;
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
        $controllerManager = $serviceLocator->getService('controllerManager');
		if (!isset(self::$tables[$key]) && !$linkTable) {
			if (isset($fields[1]) && is_array($fields[1])) {
				$linkTable = $fields[0];
				$fields = $fields[1];
			} else {
                return;
            }
			if ($fields) {
				sort($fields);
				$fromField = $fields[0];
				$toField = $fields[1];
			}
			$scheme = $modelScheme->scheme($modelName);
            $exists = (bool) $scheme;
			self::$tables[$key] = array(
				'name'		=> $linkTable,
				'field'		=> $fields,
				'exists'	=> $exists
			);
            $this->setField($joinData[0], $linkTable);
			if (!$exists) {
				$scheme = $this->scheme($fromField, $toField);
                $controllerManager->call('Model', 'scheme', array(
                    'name'      => $linkTable,
                    'fields'    => $scheme['fields'],
                    'indexes'   => $scheme['indexes']
                ));
				$controllerManager->call('Model', 'create', array(
                    'name'      => $linkTable
                ));
			}
		}
		$field = $fromField;
		$targetField = $toField;
        $queryBuilder = $serviceLocator->getService('query');
        $dds = $serviceLocator->getService('dds');
		$query = $queryBuilder
			->select($targetField)
			->from($linkTable)
			->where($field, $id);
		$ids = $dds->execute($query)->getResult()->asColumn();
        $collectionManager = $serviceLocator->getService('collectionManager');
        $keyField = $modelScheme->keyField($this->getModel());
        $itemsQuery = $queryBuilder
            ->where($keyField, $ids);
		$collection = $collectionManager->byQuery(
            $this->getModel(), $itemsQuery
        );
		return $this->resource()->setItems($collection->items());
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::resource()
	 */
	public function resource()
	{
		return new Model_Mapper_Scheme_Resource_ManyToMany($this);
	}
}