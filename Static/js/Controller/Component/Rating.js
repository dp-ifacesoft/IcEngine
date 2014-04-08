/**
 * @desc Контроллер рейтинга
 */
var Controller_Component_Rating = {
	
	/**
	 * @desc Голосование
	 * @param table Модель
	 * @param row Запись
	 * @param value Оценка
	 */
	vote: function (table, row, value)
	{
		/**
		 * @desc коллбэк с результатом оценки
		 * @param object result
		 */
		function callback (result)
		{
			var $div = $(".rating_bar_" + table + "_" + row);
			if (result.html)
			{
				$div.html (result.html);
			}
		}
		
		Controller.call (
			'Component_Rating/vote',
			{
				table:	table,
				row_id:	row,
				value:	value
			},
			callback, true
		);
	}
		
};