<?php

/**
 *
 * @author markov
 */
class Query_Part_Not_Key extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
		$key = $this->params['key'];
		$modelName = $this->modelName;
        $keyField = isset($this->params['keyField']) ?
            $this->params['keyField'] : '';
        if (!$keyField) {
            $keyField = $modelScheme->keyField($modelName);
        }
		if (!is_array($key)) {
			$this->query->where($modelName . '.' . $keyField . ' != ?',
				array($key));
		} else {
			$this->query->where($modelName . '.' . $keyField . ' NOT IN (?)',
				array($key));
		}
	}
}

