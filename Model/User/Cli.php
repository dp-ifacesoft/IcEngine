<?php

/**
 * Модель консольного пользователя
 * 
 * @author goorus, morph
 * @Service("userCli", disableConstruct=true)
 */
class User_Cli extends User
{
    /**
     * @inheritdoc
     */
	protected static $config = array(
		'fields'	=> array (
			'id'		=> -1,
			'active'	=> 1,
			'login'		=> '',
			'email'		=> '',
			'password'	=> ''
		)
	);

    /**
     * Создает новую схему
     * 
     * @param array $fields
     */
    protected function createScheme($fields)
    {
        $scheme = array();
        foreach ($fields as $fieldName => $value) {
            $scheme[$fieldName] = array(
                is_numeric($value) ? 'Int' : 'Varchar', array()
            ); 
        }
        return $fieldName;
    }
    
	/**
	 * Создает и возвращает экземпляр модели консольного пользователя
	 * 
     * @return User_Cli
	 */
	public function getInstance()
	{
        $schemeFields = $this->getService('configManager')->get(
            'Model_Mapper_User'
        )->fields;
        $configFields = $this->config()->fields->__toArray();
        if ($schemeFields) {
            $fields = array_keys($schemeFields->__toArray());
        } else {
            $scheme = $this->createScheme($configFields);
            $fields = array_keys($configFields);
            $this->getService('modelScheme')->setScheme(
                'User', new Objective($scheme)
            );
        }
        $resultFields = array();
        foreach ($fields as $fieldName) {
            $resultFields[$fieldName] = isset($configFields[$fieldName])
                ? $configFields[$fieldName] : null;
        }
		return new static($resultFields);
	}
    
    /**
     * @inheritdoc
     */
    public function hasRole($role)
    {
        return false;
    }

	/**
	 * Инициализирует модель гостя. Модель будет добавлена в менеджер ресурсов
     * 
	 * @param mixed $session_id Идентификатор сессии. Не имеет значения,
	 * параметр необходим для совместимости с User::init ().
	 */
	public function init($sessionId = null)
	{
		$instance = $this->getInstance();
        $resourceManager = $this->getService('resourceManager');
		$resourceManager->set('Model', $instance->resourceKey(), $instance);
        $this->getService('user')->setCurrent($instance);
	}

	/**
	 * @inheritdoc
	 */
	public function table()
	{
		return 'User';
	}
}