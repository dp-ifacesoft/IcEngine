<?php
/**
 * 
 * @desc Простой рендер сообщений
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Render_Simple extends Mail_Render_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Mail_Render_Abstract::render()
	 */
	public function render ($template, array $data)
	{
		return str_replace (
			array_keys ($data),
			array_values ($data),
			$template
		);
	}
	
}