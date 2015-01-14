<?php

/**
 * Входит ли элемент во множество
 * 
 * @author morph
 */
class Data_Validator_Intersect extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($data, $value = null)
    {
        $params = $this->getParams();
        $values = reset($params);
        if ($data && !in_array($data, $values)) {
            return false;
        }
        return true;
    }
}