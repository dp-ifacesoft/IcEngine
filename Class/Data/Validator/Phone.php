<?php
/**
 * Валидатор телефона
 *
 * @author Ziht
 */
class Data_Validator_Phone extends Data_Validator_Abstract
{
    public function validate($data, $value = true)
    {
        $cellPhonePattern = '/^\+?[0-9][\-\s]?(\(?[0-9]{3}\)?|[0-9]{3})[\-\s]?[0-9]{3}[\-\s]?[0-9]{4}$/';
        $phonePattern = '/^(\(?[0-9]{3,4}\)?|[0-9]{3,4})?[\-\s]?[0-9]{2,3}[\-\s]?[0-9]{2}[\-\s]?[0-9]{2}$/';
        $cellPhoneValidate = preg_match($cellPhonePattern, $data);
        $phoneValidate = preg_match($phonePattern, $data);
        return $cellPhoneValidate || $phoneValidate;
    }
}