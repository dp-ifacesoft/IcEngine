<?php

/**
 * Метод схемы связей модели, возвращающий первую модель,
 * попадающую под условие
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Method_Find_One extends Model_Mapper_Method_Abstract
{
	/**
	 * Критерия поиска модели
	 * 
     * @var array
	 */
	private $criteria = array();

	/**
	 * Добавить критерию поиска модели
	 * 
     * @param mixed
	 * @return Model_Mapper_Method_Find_One
	 */
	public function by()
	{
		$args = func_get_args();
		if (count($args) == 2) {
			$args = array($args[0] => $args[1]);
		}
		foreach ($args as $arg => $value) {
			$arg = trim($arg);
			if (!ctype_alnum($arg) && substr($arg, -1, 1) !== '=') {
				$arg .= '?';
			}
			$this->criteria[$arg] = $value;
		}
		return $this;
	}

	/**
	 * Получить модель
	 * 
     * @return Model
	 */
	public function get()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $queryBuilder = $serviceLocator->getService('query');
        $modelManager = $serviceLocator->getService('modelManager');
		$query = $queryBuilder->factory('Select');
		foreach ($this->criteria as $arg => $value) {
			$query->where ($arg, $value);
		}
		return $modelManager->byQuery($this->params[0], $query);
	}
}