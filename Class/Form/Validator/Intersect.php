<?php

/**
 * Входит ли элемент во множество
 * 
 * @author markov
 */
class Form_Validator_Intersect extends Form_Validator
{
    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        $params = $this->getParams();
        $dataValidator = $this->getDataValidator();  
        $dataValidator->setParams(array(
            $params
        ));
        return $dataValidator->validate($value);
    }
}