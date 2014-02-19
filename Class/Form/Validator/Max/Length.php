<?php

/**
 * Проверка на максимальную длину
 * 
 * @author markov
 */
class Form_Validator_Max_Length extends Form_Validator
{
    /**
     * @inheritdoc
     */
	public function validate($value)
	{
        $max = $this->getParams()[0];
        return $this->getDataValidator()->validate($value, $max);
	}
    
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        $locator = IcEngine::serviceLocator();
        $max = $this->getParams()[0];
        $plural = $locator->getService('helperPlural')
            ->plural($max, 'символа, символов, символов');
        return 'Длина значения не должна быть больше ' . $max . ' ' . $plural;
    }
}