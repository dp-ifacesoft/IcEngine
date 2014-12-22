/**
 * 
 * @desc Помощник для работы с массивами.
 * @package IcEngine
 * 
 */
Ice.Helper_Array = {
	
	/**
	 * @desc Перемешивает массив.
	 * @param a Array Массив, элементы которого будут перемешаны
	 * @param rnd Number [optional] 
	 */
	shuffle: function (a, rnd)
	{
		while (!rnd || rnd == 0)
		{
			rnd = Math.random ().toString ().substr (4);
		}
		rnd = rnd.toString ().replace ('.', '');
		do {
			rnd *= ('9' + rnd.substr (0, 1));
			rnd = rnd.toString ();
		} while (rnd.length < 10);
		var p = 0;
		a.sort (function () {
			++p;
			if (p >= rnd.length)
			{
				p = 0;
			}
			return rnd [p] > 4;
		});
	}
	
};