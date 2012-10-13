<?php

/**
 * @desc Генератор кода
 */
class Helper_Code_Generator
{
	/**
	 * @desc Генерирует код из шаблона
	 * @param string $tpl Название шаблона без .tpl
	 * @param array $data Данные для замены
	 * @return string
	 */
	public static function fromTemplate ($tpl, $data = array ())
	{
		$render = View_Render_Manager::byName ('Smarty');
		if ($data)
		{
			$render->assign ($data);
		}
		$content = $render->fetch ('Helper/Code/Generator/' . $tpl);
		unset ($render);
		return $content;
	}
}