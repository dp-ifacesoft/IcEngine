<?php

/**
 * Контролер для рендеринга заданий
 *
 * @author goorus, morph
 */
class Controller_Render extends Controller_Abstract
{
	/**
	 * Рендерим
	 */
	public function index($task)
	{
		$task->getViewRender()->render($task);
	}
}