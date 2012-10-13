<?php

function smarty_modifier_plural ($string, $forms)
{
	Loader::load ('View_Helper_Manager');
	return View_Helper_Manager::get (
		'Plural',
		array (
			'value'	=> $string, 
			'forms'	=> $forms
		)
	);
}