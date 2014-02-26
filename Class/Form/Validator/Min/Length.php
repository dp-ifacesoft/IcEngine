<?php

/**
 * Проверка на максимальную длину
 * 
 * @author markov
 */
class Form_Validator_Min_Length extends Form_Validator
{
    /**
     * @inheritdoc
     */
	public function validate($value)
	{
		$min = $this->getParams()[0];
        return $this->getDataValidator()->validate($value, $min);
	}
    
    /**
     * @inheritdoc
     */
    public function errorMessage($value = null) 
    {
        $locator = IcEngine::serviceLocator();
        $min = $this->getParams()[0];
        $plural = $locator->getService('helperPlural')
            ->plural($min, 'символа, символов, символов');
        return 'Длина значения не должна быть меньше ' . $min . ' ' . $plural;
    }
}