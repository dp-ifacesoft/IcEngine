<?php

/**
 * По полю phpSessionId
 *
 * @author neon
 */
class Query_Part_Session extends Query_Part
{

	/**
	 * @inheritdoc
	 */
	public function query()
	{
        if (isset($this->params['id'])) {
            $sessionId = $this->params['id'];
        } else {
            $locator = IcEngine::serviceLocator();
            $session = $locator->getService('userSession')->getCurrent();
            $sessionId = $session->key();
        }
		$this->query->where($this->modelName . '.phpSessionId', $sessionId);
	}

}