<?php

/**
 * Механизм валидации данных
 *
 * @author morph
 * @Service("dataValidator")
 * @ServiceAccessor
 */
class Data_Validator
{
    /**
     * Менеджер валидаторов данных
     * 
     * @var Data_Validator_Manager
     * @Generator 
     * @Inject
     */
    protected $dataValidatorManager;
    
    /**
     * Валидация модели
     *
     * @param array $data
     * @param array $scheme Схема валидации
     * @return boolean|array
     */
	public function validate($data, $scheme)
	{
        $tasks = $this->getService('controllerManager')->getTaskPool();
        end($tasks);
        $last = current($tasks);
        $input = $last->getInput();
		$validate = array();
		$error = false;
        $messages = isset($scheme['messages']) ? $scheme['messages'] : array();
		foreach ($scheme as $fieldName => $validators) {
			$current = array(
				'valid'		=> true,
				'errors'	=> array()
			);
            if (!isset($data[$fieldName])) {
                $current['valid'] = false;
                $validate[$fieldName] = $current;
                continue;
            }
			foreach ($validators as $validatorName => $value) {
                $args = array($data[$fieldName]);
                $params = array();
                if (is_numeric($validatorName)) {
                    $validatorName = $value;
                } elseif (is_array($value)) {
                    $params = $value;
                } elseif (strpos($value, '::') === 0) {
                    $value = $input[substr($value, 2)];
                    $args[] = $value;
                } else {
                    $args[] = $value;
                }
                $validator = $this->dataValidatorManager->get($validatorName);
                if ($params) {
                    $validator->setParams($params);
                }
                $result = call_user_func_array(
                    array($validator, 'validate'), $args
                );
                $current['valid'] &= $result;
                if (!$result) {
                    $error = true;
                    $current['errors'][$validatorName] = isset(
                        $messages[$fieldName], 
                        $messages[$fieldName][$validatorName]
                    ) ? $messages[$fieldName][$validatorName] : true;
                }
            }
			$validate[$fieldName] = $current;
		}
		if (!$error) {
			return true;
		}
		return $validate;
	}
    
    /**
     * Getter for "dataValidatorManager"
     *
     * @return Data_Validator_Manager
     */
    public function getDataValidatorManager()
    {
        return $this->dataValidatorManager;
    }
        
    /**
     * Setter for "dataValidatorManager"
     *
     * @param Data_Validator_Manager dataValidatorManager
     */
    public function setDataValidatorManager($dataValidatorManager)
    {
        $this->dataValidatorManager = $dataValidatorManager;
    }
    
    /**
     * Get service by name
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
}