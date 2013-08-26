<?php

/**
 * Выводит слово с нужным окончанием
 *
 * @author neon
 * @param int $number
 * @param array $forms
 * @return string
 */
function smarty_modifier_plural($number, $forms)
{
	$locator = IcEngine::serviceLocator();
    $helperPlural = $locator->getService('helperPlural');
	return $helperPlural->plural($number, $forms);
}