<?php

/**
 * Запроса типа select
 *
 * @author morph, goorus
 */
class Query_Select extends Query_Abstract
{
    /**
     * @inheritdoc
     */
    protected $type = Query::SELECT;

	/**
	 * @inheritdoc
	 */
	public function getTags()
	{
		$tags = array();
		$from = $this->getPart(Query::FROM);
		if (!$from) {
			return;
		}
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
		foreach ($from as $info) {
			$tags[] = $modelScheme->table($info[Query::TABLE]);
		}
		return array_unique($tags);
	}

    /**
     * @inheritdoc
     */
    public function tableName()
    {
        $fromPart = $this->getPart(Query::FROM);
        $keys = array_keys($fromPart);
        $tableName = reset($keys);
        return $tableName;
    }
}