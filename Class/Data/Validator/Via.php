<?php

/**
 * Валидация через стронний метод
 * 
 * @author morph
 */
class Data_Validator_Via extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value)
	{
        $result = true;
        $serviceLocator = IcEngine::serviceLocator();
        foreach ((array) $value as $validator) {
            list($serviceName, $method) = explode('/', $validator);
            $service = $serviceLocator->getService($serviceName);
            $result &= call_user_func_array(
                array($service, $method), array($data)
            );
            if (!$result) {
                break;
            }
        }
		return $result;
	}
}