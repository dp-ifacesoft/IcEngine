<?php

/**
 * Рендер для AJAX запросов
 *
 * @author neon
 */
class View_Render_Ajax extends View_Render_Abstract
{

    /**
     * Получить контент вида
     *
     * @param string $tpl
     * @return array
     */
	public function fetch($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array();
		return json_encode($result);
	}

    /**
     * Отправить на вывод контент
     *
     * @param string $tpl
     * @return string
     */
	public function display($tpl)
	{
		reset($this->_vars);
		echo json_encode(current($this->_vars));
	}
}