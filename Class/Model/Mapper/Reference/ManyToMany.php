<?php

/**
 * Тип ссылки "многие-ко-многим"
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Reference_ManyToMany extends 
    Model_Mapper_Reference_Abstract
{
    /** 
     * @inheritdoc
     */
    public function execute()
    {
        $dto = $this->getService('dto')->newInstance()
            ->set(array(
               'fromField'        => $this->args['links']['fromField'], 
                'toField'    => $this->args['links']['toField'],
                'modelName'  => $this->getName(),
                'toJoinField' => $this->args['links']['toJoinField'],
                'fromJoinField' => $this->args['links']['fromJoinField'],
                'JoinTable' => $this->args['links']['JoinTable']
            ));
        return new Model_Mapper_Reference_State_ManyToMany($this->model, $dto);
    }
    
	/**
	 * Сформировать ключ связи "многие-ко-многим"
	 * 
     * @param string $model_name
	 * @return string
	 */
	protected function getJoinTable($modelName)
	{
		$keyTable = array($modelName, $this->model->modelName());
		sort($keyTable);
		$key = implode('_', $keyTable);
		$postfix = abs(crc32($keyTable[0]) % crc32($keyTable[1]));
		$key .= $postfix;
		return $key;
	}
    
    /**
     * @inheritdoc
     */
    public function setArgs($args)
    {
         if (!isset($args['links'])) {
            $args  = $args['links'];
        }
        parent::setArgs($args);
    }  
}