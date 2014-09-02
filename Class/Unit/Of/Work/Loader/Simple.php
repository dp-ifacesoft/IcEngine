<?php

/**
 * Загрузчик для Unit_Of_Work по умолчанию
 * Пока что в нём только byKey {id: }
 *
 * @author neon
 */
class Unit_Of_Work_Loader_Simple extends Unit_Of_Work_Loader_Abstract
{
	/**
	 * Простая загрузка
	 *
	 * @inheritdoc
	 * @param string $uniqName
	 * @param array $raw
	 */
	public function load($uniqName, $result)
	{
		$locator = IcEngine::serviceLocator();
		$unitOfWork = $locator->getService('unitOfWork');
		$modelScheme = $locator->getService('modelScheme');
		$configManager = $locator->getService('configManager');
		$resourceManager = $locator->getService('resourceManager');
		$rawData = $unitOfWork->getRaw(QUERY::SELECT, $uniqName);
		reset($rawData);
		$raws = current($rawData);
		$results = $result->getResult()->asTable();
		list($modelName, $keyField) = explode('@', $uniqName);
		$keyField = $modelScheme->keyField($modelName);
		$resultArray = array();
		foreach ($results as $result) {
			$resultArray[$result[$keyField]] = $result;
		}
		$config = $configManager->get('Model_Mapper_' . $modelName);
		$fields = array_keys($config->fields->asArray());
		foreach ($raws as $raw) {
			$object = &$raw['object'];
			$fieldsPrepared = array();
			foreach ($fields as $field) {
				if (isset($resultArray[$object->key()][$field])) {
					$fieldsPrepared[$field] = 
                        $resultArray[$object->key()][$field];
				} else {
					$fieldsPrepared[$field] = strrpos($field, '__id') 
                        ? 0 : null;
				}
			}
			$object->set($fieldsPrepared);
			$object->setLoaded(true);
			$resourceManager->set(
				'Model',
				$modelName . '__' . $object->key(),
				$object
			);
		}
	}
}