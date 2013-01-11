<?php

/**
 * Реализация ORM
 *
 * @author morph, neon
 * @Service("modelMapper")
 */
class Model_Mapper extends Manager_Abstract
{
	/**
	 * Схемы связей моделей
	 * @var array
	 */
	private $schemes = array();

	/**
	 * (non-PHPDoc)
	 */
	public function __call($method, $params)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelMapperMethod = $serviceLocator->getService('modelMapperMethod');
		$methodName = $modelMapperMethod->normalizaName($method);
		$conreteMethod = $modelMapperMethod->byName($methodName);
		$conreteMethod->setParams($params);
		return $method->execute();
	}

	/**
	 * Получить схему для моделей
	 *
	 * @param string $model_name
	 */
	public function scheme($model)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $configManager = $serviceLocator->getService('configManager');
        $modelMapperScheme = $serviceLocator->getService('modelMapperScheme');
        $modelMapperSchemePart = $serviceLocator->getService(
            'modelMapperSchemePart'
        );
		if (is_object($model)) {
			$modelName = $model->modelName();
		} else {
			$modelName = $model;
		}
		$key = $modelName . '_' . $model->key();
		if (!isset($this->schemes[$key])) {
			$config = $configManager->get('Model_Mapper_' . $modelName);
			if (!$config) {
				return;
			}
			$schemeName = 'Simple';
			if (isset($config->scheme)) {
				$schemeName = $config->scheme;
			}
			$scheme = $modelMapperScheme->byName($schemeName);
			$scheme->setModel($model);
			foreach ($config as $name => $values) {
				if ($values) {
					$scheme = $modelMapperSchemePart->getAuto(
						$name, $scheme, $values
					);
				}
			}
			$this->schemes[$key] = $scheme;
		}
		return clone $this->schemes[$key];
	}
}