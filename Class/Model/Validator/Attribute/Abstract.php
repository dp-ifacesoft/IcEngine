<?php

abstract class Model_Validator_Attribute_Abstract
{
	abstract public static function validate ($model, $field, $value, $input);
}