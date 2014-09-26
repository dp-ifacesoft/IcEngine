/**
 * @desc Помощник для работы с датой
 */
var Helper_Date = {
		
	/**
	 * @desc Преобразование строки в дату
	 * @param string str Исходная стока, содержащая дату
	 * @param mixed def Возвращаемое по умолчанию значение.
	 * @returns Дата
	 */
	parse : function (str, def)
	{
		if (typeof (str) == "object" && str instanceof Date)
		{
			return str;
		}

		if (str == parseInt (str))
		{
			return new Date (str);
		}

		if (!str || str.length < 8)
		{
			return new Date (def ? def : 0);
		}

		var arr = str.split (/[\D]/);

		for (var i = 0; i <= 5; ++i)
		{
			arr [i] = arr [i] ? parseInt (arr [i] * 1) : 0;
		}

		if (arr[0] > 999)
		{
			// Y-m-d H:i:s
			return new Date (arr[0], arr[1] - 1, arr[2], arr[3], arr[4], arr[5]);
		}
		else if (arr [2] > 999)
		{
			// d.m.Y H:i:s
			return new Date (arr[2], arr[1] - 1, arr[0], arr[3], arr[4], arr[5]);
		}
		else if (arr [3] > 999)
		{
			// H:i:s Y-m-d
			return new Date (arr[3], arr[4] - 1, arr[5], arr[0], arr[1], arr[2]);
		}
		else if (arr [5] > 999)
		{
			// H:i:s d.m.Y
			return new Date (arr[5], arr[4] - 1, arr[3], arr[0], arr[1], arr[2]);
		}

		return new Date (def);
	},
	
	/**
	 * @desc Приводит дату к формату "dd.mm.yyyy"
	 * @param mixed time Дата в произвольном формате.
	 * @param string sep разделитель.
	 * @returns string Дата в формате "dd.mm.yyyy"
	 */
	format_ddmmyyyy : function (time, sep)
	{
		var date = strToDate (time);
		var d = date.getDate () + "";
		var m = date.getMonth () - -1 + "";
		var y = date.getFullYear ();

		if (!sep)
		{
			sep = ".";
		}

		return (d.length == 1 ? "0" + d : d) + sep +
			(m.length == 1 ? "0" + m : m) + sep +
			y;
	}
};

strToDate = function (str, def)
{
	return Helper_Date.parse (str, def);
};

dateFormat_ddmmyyyy = function (time, sep)
{
	return Helper_Date.format_ddmmyyyy (time, sep);
};