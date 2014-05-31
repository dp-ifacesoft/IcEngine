<?php

/**
 * Представление рендера схемы связей модели для Mysql
 *
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Render_Mysql extends
    Model_Mapper_Scheme_Render_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Render_View_Abstract::render
	 */
	public function render($modelName)
	{
        $serviceLocator = IcEngine::serviceLocator();
		$modelConfig = $serviceLocator->getService('configManager')->get(
            'Model_Mapper_' . $modelName
        );
		if (!$modelConfig) {
			return;
		}
		$query = $serviceLocator->getService('query')
			->createTable($modelName);
        $schemeFields = $modelConfig['fields']->__toArray();
		foreach ($schemeFields as $fieldName => $values) {
			$field = new Model_Field($fieldName);
			$field->setType($values[0]);
			$attr = $values[1];
			foreach ($attr as $key => $value) {
				if (is_numeric($key)) {
					unset($attr[$key]);
					$attr[$value] = true;
				}
			}
			if (!empty($attr['Size'])) {
				$field->setSize($attr['Size']);
			}
			if (!empty($attr['Enum'])) {
				$field->setEnum($attr['Enum']);
			}
			$field->setNullable(!empty($attr['Null']));
			if (!empty($attr['Unsigned'])) {
				$field->setUnsigned(true);
			}
			if (!empty($attr['Charset'])) {
				$field->setCharset($attr['Charset']);
			}
			if (isset($attr['Default'])) {
				$field->setDefault($attr['Default']);
			}
			if (!empty($attr['Comment'])) {
				$field->setComment($attr['Comment']);
			}
			if (!empty($attr['Auto_Increment'])) {
				$field->setAutoIncrement(true);
			}
			$query->addField($field);
		}
		if ($modelConfig['indexes']) {
            $schemeIndexes = $modelConfig ['indexes']->__toArray();
			foreach ($schemeIndexes as $indexName => $values) {
				$index = new Model_Index($indexName . '_index');
				$index->setType($values[0]);
				$index->setFields($values[1]);
				$query->addIndex($index);
			}
		}
		return $query;
	}
}