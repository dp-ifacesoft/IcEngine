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
     * @return boolean
     */
    isExists: function(email)
    {
        var timer, processing = 1, output = false;
        if (!email) {
            return false;
        }
        function callback(result) {
            if (result.data.isExist) {
                output = true;
            }
            console.log('out');
            processing = 0;
        }
        Controller.call(
            'Email_Exists',
            {
                'email': email
            },
            callback,
            true
        );
        timer = setInterval(
            function() {
                if (processing == 0) {
                    clearInterval(timer);
                }
            },
            3100
        );
        console.log(output);
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