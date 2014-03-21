<?php

/**
 * Ошибка на максимальную длину
 * 
 * @author markov
 */
class Validator_Error_Min_Length extends Validator_Error
{    
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