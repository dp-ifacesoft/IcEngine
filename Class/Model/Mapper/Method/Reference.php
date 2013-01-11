<?php

/**
 * Создание схемы ссылки
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Method_Reference extends Model_Mapper_Method_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Method_Abstract::execute
	 */
	public function execute()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelMapperSchemePart = $serviceLocator->getService(
            'modelMapperSchemePart'
        );
		$part = $modelMapperSchemePart->byName('Reference');
		return $part->set(
            $this->params[0],
			$this->params[1]['Target'],
			isset($this->params[1]['JoinColumn']) 
                ? $this->params[1]['JoinColumn'] : null,
            isset($this->params[1]['JoinTable'])
                ? $this->params[1]['JoinTable'] : null
        );
	}
}