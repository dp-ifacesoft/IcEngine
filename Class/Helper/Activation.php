<?php
/**
 * @Service("helperActivation")
 * @desc Помощник активации.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Activation
{

	/**
	 * @desc Генерация циферного кода для активации.
	 * Генерируется случайное число длинной от $from до $to,
	 * поэтому чаще будут генерироваться числа с максимальным количеством
	 * знаков.
	 * @param integer $from Минимальное количество цифр.
	 * @param integer $to Максимальное количество цифр.
	 */
	public function generateNumeric ($from = 5, $to = 7)
	{
		return rand (
			str_pad ("1", $from, '0'),	// от 10000
			str_repeat ('9', $to)		// до 9999999
		);
	}

	/**
	 * @desc Создание активации с коротким кодом.
	 * @param string $prefix Префикс для кода.
	 * @param integer $from Минимальное количество символов.
	 * @param integer $to Максимальное количество символов.
	 * @return string Свободный код (с префиксом)
	 */
	public function newShortCode ($prefix, $from = 5, $to = 7)
	{
		do {
			$code = $prefix . self::generateNumeric ($from, $to);
			$activation = Activation::byCode ($code);
		} while ($activation);

		return $code;
	}

	/**
	 * @desc Поиск активации по префиксу и короткому коду.
	 * @param string $prefix
	 * @param string $code
	 * @return Activation
	 */
	public function byShortCode ($prefix, $code)
	{
		return Activation::byCode ($prefix . $code);
	}

    /**
     * Осуществить активацию
     *
     * Допустим, незарегистрированный пользователь оставляет комментарий на сайте. Чтобы мы были уверены,
     * что пользователь указал верный email, он должен сообщить нам код активации, который мы отправили ему
     * на указанный им email. Если код оказался верным, мы помечаем созданную ранее запись активации как исполненную,
     * и возвращаем массив параметров произведенной активации вызвавшему метод сценарию.
     * Однако, может быть, пользователь повторно перешел по ссылке из своего email, мы запустили данный метод,
     * а выясняется, что активация уже произведена. Что делать - выдавать ошибку или имитировать успешную активацию?
     * Пусть решает вызвавший скрипт. У нас есть параметр $excludingErrors - массив с кодами ошибок, которые
     * метод должен игнорировать. Чтобы повторая попытка активации не считалась ошибкой и вызвавший скрипт
     * получал параметры записи активации, вызывать метод следует с $excludingErrors=array('alreadyActivated'),
     * и так с любыми другими возможными ошибками.
     * Возвращаемые коды ошибок (если не помечены для игнорирования в $excludingErrors):
     *  - activationNotFound: модель активации с id=$activationId не существует.
     *  - alreadyActive: модель активации с id=$activationId уже активирована.
     *  - wrongCode: указанный $code не совпадает с кодом модели активиции $activationId.
     *
     * @param integer $activationId ID записи об активации
     * @param integer $code Код активации
     * @param array $excludingErrors Ошибки активации, которые стоит игнорировать (считать нормальной работой метода)
     * @return array|string Массив параметров активации, если всё ОК, либо строковый код ошибки
     */
    public function activate($activationId, $code, array $excludingErrors = array())
    {
        /** @var Model_Manager $modelManager */
        $modelManager = IcEngine::serviceLocator()->getService('modelManager');
        /** @var Activation $activation */
        $activation = $modelManager->byKey('Activation', $activationId);
        $result = null;
        $isError = false;
        if (!$activation && !in_array('activationNotFound', $excludingErrors)) {
            $result = 'activationNotFound';
            $isError = true;
        }
        if ($activation['finished'] && !in_array('alreadyActivated', $excludingErrors)) {
            $result = 'alreadyActivated';
            $isError = false;
        }
        if ($activation['code'] != $code && !in_array('wrongCode', $excludingErrors)) {
            $result = 'wrongCode';
            $isError = true;
        }
        if (!$isError) {
            $activation->activate();
            $result = json_decode($activation['paramsJson'], true);
        }
        return $result;
    }

}