/**
 *
 * @desc Хелпер для объектов.
 * @package IcEngine
 * 
 */
Ice.Helper_Object = {
	
	/**
	 * @param object object Объект, из которого будут удалены элементы
	 * @param offset integer Индекс первого элемента для удаления.
	 * @param length integer [optional] количество удаляемых элементов,
	 * если не передано - будут удалены все элементы до конца объекта.
	 * @return object объект, содержащий удаленных элементы
	 */
	splice: function (object, offset, length)
	{
		if (typeof object [offset] == 'undefined')
		{
			return {};
		}
		
		var result = {};
		var result_i = 0;
		
		if (typeof length == 'undefined')
		{
			while (typeof object [offset] != 'undefined')
			{
				result [result_i] = object [offset];
				delete object [offset];
				++offset;
				++result_i;
			}
		}
		else
		{
			var pos = offset;
			while (typeof object [pos] != 'undefined')
			{
				if (result_i < length)
				{
					result [result_i] = object [pos];
					++result_i;
				}
				else
				{
					object [pos - result_i] = object [pos];
				}
				delete object [pos];
				++pos;
			}
		}
		
		return result;
	}
	
};