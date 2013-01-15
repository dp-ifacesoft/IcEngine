/**
 * Помощник для работы с email
 *
 * @author neon, Юрий Шведов
 * @package IcEngine
 */
var Helper_Email = {

    /**
     * Проверяет на существование пользователя с таким email
     * Синхронный метод
     *
     * @param email string
     * @return boolean
     */
    isExists: function(email)
    {
        var output = false;
        if (typeof email == undefined || !email) {
            return output;
        }
        Loader.load('Sync', 'engine');
        Controller._transports.push(Sync);
        Controller.call(
            'Email_Exists',
            {
                'email': email
            },
            function (result) {
                Controller._transports.pop();
                if (result.data.isExists) {
                    output = true;
                }
            },
            true
        );
        return output;
    },

    /**
     * Проверяет email на валидность
     *
     * @param email string
     * @return boolean
     */
    isValid: function(email)
    {
        if (!Helper_Email.parseEmail(email)) {
            return false;
        }
        return true;
    },

	/**
	 * Проверяет емейл на корректность.
	 *
	 * @param email string
	 * @returns string|false
	 */
	parseEmail: function(email)
	{
        if (typeof(email) == 'undefined') {
            return false;
        }
		var p_at = email.indexOf('@');
		if (p_at < 0) {
			return false;
		}
		var p_dot = email.indexOf ('.', p_at);
		if (p_dot < 0 || p_dot > email.length - 2) {
			return false;
		}
		return email;
	}
};