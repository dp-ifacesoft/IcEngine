/**
 * Помощник для работы с email
 *
 * @author Юрий Шведов
 * @package IcEngine
 */
var Helper_Email = {

	/**
	 * Проверяет емейл на корректность.
	 *
	 * @param string email
	 * @returns string|false
	 */
	parseEmail: function (email)
	{
		var p_at = email.indexOf ('@');

		if (p_at < 0)
		{
			return false;
		}

		var p_dot = email.indexOf ('.', p_at);

		if (p_dot < 0 || p_dot > email.length - 2)
		{
			return false;
		}

		return email;
	}
};