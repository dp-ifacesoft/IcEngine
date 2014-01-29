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
		$result = $this->vars;
		$this->vars = array();
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
		reset($this->vars);
		echo json_encode(current($this->vars));
	}
}