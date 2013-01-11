<?php

/**
 * Часть схемы моделей, отвечающая за ссылки
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Part_Reference extends 
    Model_Mapper_Scheme_Part_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::$_specification
	 */
	protected static $specification = 'references';

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::set
	 */
	public function set($name, $target, $column, $joinTable)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $schemeReference = $serviceLocator->getService(
            'modelMapperSchemeReference'
        );
		$reference = $schemeReference->byName($name);
		$reference->setModel($target);
		$reference->setField(array($column, $joinTable));
		return $reference;
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::execute
	 */
	public function execute($scheme, $values)
	{
		foreach ($values as $name => $params) {
			$scheme->$name = $this->set(
				$params[0],
				$params[1]['Target'],
                isset($params[1]['JoinColumn'])
                    ? $params[1]['JoinColumn'] : null,
                isset($params[1]['JoinTable'])
                    ? $params[1]['JoinTable'] : null
			);
		}
		return $scheme;
	}
}