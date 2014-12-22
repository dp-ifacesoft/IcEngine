<?php

/**
 * 
 * @author markov
 */
class Query_Part_Not_Table extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
		$table = $this->params['table'];
		$modelName = $this->modelName;
		if (!is_array($table)) {
			$this->query->where($modelName . '.table != ?',
				array($table));
		} else {
			$this->query->where($modelName . '.table NOT IN (?)',
				array($table));
		}
	}
}