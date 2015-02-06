<?php
/**
 * Сервис недостающего функционала по Data_Transport
 *
 * @author LiverEnemy
 *
 * @Service("serviceDataTransport")
 *
 * upd: Аннотация Service была добавлена ради совместимости с функционалом статического класса App
 */

class Service_Data_Transport extends Service_Abstract {
    /**
     * Получить данные из Data_Transport, даже если имя интересующего GET-параметра иерархическое
     *
     * Например, если $paramName == 'name[model][field]', обычный Data_Transport::receive() вернет непонятно что,
     * а данный метод получит массив значений по индексу name и найдет среди них требуемое.
     *
     * @param Data_Transport $dataTransport
     * @param                $paramName
     *
     * @return string
     */
    public function receiveFromHierarchical(Data_Transport $dataTransport, $paramName)
    {
        $nameParts = [];
        /**
         * Будем разбивать (tokenize) имя на элементы массива и получим данные для начального элемента массива:
         * Data_Transport::receive() большим интеллектом не обладает
         */
        $tok = strtok($paramName, "[]");
        while ($tok !== false) {
            $nameParts[] = $tok;
            $tok = strtok("[]");
        }
        $value = $dataTransport->receive($nameParts[0]);
        /**
         * Вот он - признак того, что наш индекс является массивом, а не строкой:
         * входные данные, полученные только что по $nameParts[0], являются массивом.
         * Чтобы получить введенное строковое значение фильтра, будем копаться в массиве, как в кабачковой икре.
         */
        if (is_array($value))
        {
            for ($i = 1; $i < count($nameParts); $i++)
            {
                $namePart = $nameParts[$i];
                $value = $value[$namePart];
            }
        }
        $value = urldecode($value);
        return $value;
    }
} 