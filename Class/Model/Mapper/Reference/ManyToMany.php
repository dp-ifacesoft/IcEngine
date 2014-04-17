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
                'Target'        => $this->args['Target'], 
                'JoinColumn'    => $this->args['JoinColumn'],
                'JoinTable'     => $this->args['JoinTable']
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
        if (!isset($args['Target'])) {
            $args['Target'] = $this->getService('helperService')->normalizeName(
                $this->field
            );
        }
        if (!isset($args['JoinColumn'])) {
            $modelName = $this->model->modelName();
            $modelScheme = $this->getService('modelScheme');
            $selfKeyField = $modelScheme->keyField($modelName);
            $args['JoinColumn'] = array(
                $modelName . '__' . $selfKeyField
            );
            $otherKeyField = $modelScheme->keyField($args['Target']);
            $args['JoinColumn']['on'] = $args['Target'] . '__' . 
                $otherKeyField;
        }
        if (!isset($args['JoinTable'])) {
            $args['JoinTable'] = $this->getJoinTable($args['Target']);
        }
        parent::setArgs($args);
    }  
}