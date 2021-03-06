<?php

/**
 * Ошибка на максимальную длину
 * 
 * @author markov
 */
class Form_Validator_Error_Test_Min_Length extends Form_Validator_Error
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
        return 'бла ' . $min . ' ' . $plural;
    }
}