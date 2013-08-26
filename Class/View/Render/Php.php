<?php

/**
 * Рендер php
 * 
 * @author morph
 */
class View_Render_Php extends View_Render_Abstract
{
	/**
     * @inheritdoc
     */
	public function fetch($tpl)
	{
		$result = '';
		foreach ($this->templatesPathes as $path) {
			$file = $path . $tpl;
			if (file_exists($file)) {
				ob_start();
				include $file;
				$result = ob_get_contents();
				ob_end_clean();
				break;	
			}
		}
		return $result;
	}
	
    /**
     * @inheritdoc
     */
	public function display ($tpl)
	{
		foreach ($this->templatesPathes as $path) {
			$file = $path . $tpl;
			if (file_exists($file)) {
				foreach ($this->vars as $key => $value) {
					$$key = $value;
				}
				include $file;
				return;
			}
		}
	}
}