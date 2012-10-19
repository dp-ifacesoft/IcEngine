<?php

class View_Helper_Widget extends View_Helper_Abstract
{

	/**
	 * Метод виджета по умолчанию.
	 *
	 * @var string
	 */
	const DEFAULT_METHOD = 'index';

//	{Widget call="Page"} = {Widget call="Page->index"}
//	{Widget call="Page->get" var="var"}

	/**
	 * Вызывает метод виджета.
	 * @param array $params
	 * 		$params ['call']
	 * 			Вызываемый метод виджета или название виджета.
	 * 		Может быть передано в виде:
	 * 		1) Widget::staticMethod
	 * 			Вызов статического метода.
	 * 			Будет вызван статический метод класса. Объект виджета создан
	 * 			не будет.
	 * 			Пример: {Widget call="Widget::staticMethod"}
	 * 		2) Widget->dynamicMethod или Widget/dynamicMethod
	 * 			Вызов динамического метода.
	 * 			Будет создан объект виджета (либо использован ранее созданный).
	 * 			Пример: {Widget call="Widget->dynamicMethod"}
	 * 			{Widget call="Widget/dynamicMethod"}
	 * 		3) Widget
	 *			Вызов динамического метода по умолчанию.
	 *			Аналогично вызову динамического метода с именем "index".
	 *			Пример: {Widget call="Widget"}
	 *			Синоним: {Widget call="Widget->index"}
	 * 		$params ['var'] [optional]
	 * 			Переменная результата.
	 *
	 * 		Все параметры (включая "call" и "var") будут переданы
	 * 		в метод виджета.
	 * @return mixed
	 * 		Если задан параметр "var", результат виджета будет
	 * 		помещен в соответсвующую переменную текущего вьювера.
	 * 		Если параметр "var" не указан, результат будет вставлен
	 * 		по месту вызова виджета.
	 */
	public function get (array $params)
	{
		$call = $params ['call'];

		$p = strpos ($call, '::');

		if ($p)
		{
			// Явно указан статический метод виджета
			$widget = substr ($call, 0, $p);
			$method = substr ($call, $p + 2);
		}
		else
		{
			$p = strpos ($call, '->');

			if ($p)
			{
				// Явно указан метод виджета
				$widget = substr ($call, 0, $p);
				$method = substr ($call, $p + 2);
			}
			else
			{
				$p = strpos ($call, '/');
				if ($p)
				{
					$widget = substr ($call, 0, $p);
					$method = substr ($call, $p + 1);
				}
				else
				{
					// Указано только название виджета, вызываем метод index
					$widget = $call;
					$method = self::DEFAULT_METHOD;
				}
			}
		}

		$result = Widget_Manager::call ($widget, $method, $params, false);

		if (isset ($params ['var']))
		{
			$this->_view->assign ($params ['var'], $result ['return']);
		}
		else
		{
			return $result ['html'];
		}
	}

}