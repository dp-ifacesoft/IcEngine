<?php

/**
 * Метод схемы связей модели, возвращающий коллекцию моделей,
 * попадающую под условие
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Method_Find extends Model_Mapper_Method_Abstract
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
	 * @return Model_Mapper_Method_Find
	 */
	public function by()
	{
		$args = func_get_args ();
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
	 * Получить коллекцию
	 * 
     * @return Model_Collection
	 */
	public function get()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $collectionManager = $serviceLocator->getService('collectionManager');
        $queryBuilder = $serviceLocator->getService('query');
		$query = $queryBuilder->factory('Select');
		foreach ($this->criteria as $arg => $value) {
			$query->where($arg, $value);
		}
		return $collectionManager->byQuery($this->params[0], $query);
	}
}