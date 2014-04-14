<?php

/**
 * Запрос типа update
 *
 * @author morph, goorus
 */
class Query_Update extends Query_Select
{
    /**
     * @inheritdoc
     */
    protected $type = Query::UPDATE;

	/**
	 * @inheritdoc
	 */
	public function getTags()
	{
		$tags = array();
        $modelScheme = IcEngine::serviceLocator()->getService('modelScheme');
		$update = $this->getPart(Query::UPDATE);
		if ($update) {
	   		$tags[] = $modelScheme->table($update);
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