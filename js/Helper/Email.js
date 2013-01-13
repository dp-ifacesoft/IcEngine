/**
 * Помощник для работы с email
 *
 * @author neon, Юрий Шведов
 * @package IcEngine
 */
var Helper_Email = {

    /**
     * Проверяет на существование пользователя с таким email
     *
     * @param email string
     * @param callback function
     */
    isExists: function(email, callback)
    {
        if (!email) {
            callback.call(null, false);
        }
        Controller.call(
            'Email_Exists',
            {
                'email': email
            },
            function (result) {
                if (result.data.isExists) {
                    callback.call(null, true);
                    return;
                }
                callback.call(null, false);
            },
            true
        );
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