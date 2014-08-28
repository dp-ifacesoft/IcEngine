<?php

/**
 * Ошибка на максимальную длину
 * 
 * @author markov
 */
class Validator_Error_Max_Length extends Validator_Error
{    
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