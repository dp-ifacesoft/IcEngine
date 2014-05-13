<?php

/**
 * Запрос типа replace
 *
 * @author morph, goorus
 */
class Query_Replace extends Query_Select
{
    /**
     * @inheritdoc
     */
    protected $type = Query::REPLACE;

	/**
	 * @inheritdoc
	 */
	public function getTags()
	{
		$tags = array();
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
		$replace = $this->getPart(Query::REPLACE);
		if ($replace) {
	   		$tags[] = $modelScheme->table($replace);
		}
		return array_unique($tags);
    }

    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return $this->getPart($this->type);
    }
}