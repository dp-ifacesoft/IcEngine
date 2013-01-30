<?php

function smarty_modifier_plural ($string, $forms)
{
	return View_Helper_Manager::get (
		'Plural',
		array (
			'value'	=> $string,
			'forms'	=> $forms
		)
	);
}