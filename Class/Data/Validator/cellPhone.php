<?php

/**
 * cellPhone - является ли мобильным номером
 *
 * @author Apostle
 */
class Data_Validator_cellPhone extends Data_Validator_Abstract
{
    /**
     * 
     * @inheritdoc
     */
    public function validate($data, $value = null)
    {
        $cellPhonePattern = '/^\+?[0-9][\-\s]?(\(?[0-9]{3}\)?|[0-9]{3})[\-\s]?[0-9]{3}[\-\s]?[0-9]{4}$/';
        $cellPhoneValidate = preg_match($cellPhonePattern, $data);
        return $cellPhoneValidate;
    }
}