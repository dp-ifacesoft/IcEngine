<?php
/**
 *
 * @desc Помощник для работы с телефонными номерами
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Phone extends Helper_Abstract
{

    /**
     * @desc Длина номера мобильного телефона.
     * @var integer
     */
    public static $mobileLength =
        array(
            11, // Россия
            12, // Крым
        );

    /**
     * @desc Возвращает номер мобильного телефона в формате "+7 123 456 78 90"
     * @param string $phone 11/12 цифр номера
     * @return string Отформатированный номер телефона.
     */
    public function formatMobile($phone)
    {
        return
            '+' .
            substr($phone, 0, -10) . ' ' .
            substr($phone, -10, 3) . ' ' .
            substr($phone, -7, 3) . ' ' .
            substr($phone, -4, 2) . ' ' .
            substr($phone, -2, 2);
    }

    /**
     * @desc Поиск в строке номера мобильного телефона
     * @param string $str
     * @tutorial
     *        parseMobile ("+7 123 456 78 90") = 71234567890
     *        parseMobile ("8-123(456)78 90") = 71234567890
     *        parseMobile ("61-61-61") = false
     * @return string|false Номер телефона или false.
     */
    public static function parseMobile($str)
    {
        if (strlen($str) < self::$mobileLength[0]) {
            return false;
        }

        $i = 0;
        $c = $str [0];
        $result = "";

        if ($c == "+") {
            $i = 1;
        } else if ($c == "8") {
            // Россия, номер начинается с 8
            $i = 1;
            $result = "7";
        }

        $digits = "0123456789";
        $ignores = "-() +";
        for (; $i < strlen($str); ++$i) {
            $c = $str [$i];
            if (strpos($digits, $c) !== false) {
                $result .= $c;
            } else if (strpos($ignores, $c) === false) {
                return false;
            }
        }

        return in_array(strlen($result), self::$mobileLength) ? $result : false;
    }

}