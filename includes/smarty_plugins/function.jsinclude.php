<?php
/**
 * @desc Плагин смарти для вызова js скриптов.
 * @param array $params
 * @return string
 */
function smarty_function_jsinclude (array $params)
{
	$file = str_replace ('_', '/', $params ['call']);
	return 
		'<script type="text/javascript">' .
			"(function (){\n" .
				'var Input = ' . json_encode ($params) . ";\n" .
				file_get_contents ($file) . 
			'}())' .
		'</script>';
}
