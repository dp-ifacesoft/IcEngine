<?php/** * Хелпер для компиляции строки или массива, * преобразующий переменные в строках подходящими значениями * * @author LiverEnemy * @Service ("helperCompile") */class Helper_Compile extends Helper_Abstract {        /**     * Скомпилировать строку с использованием контроллер-экшена     * @param Array|string $what - исходная строка или массив строк     * @param type $withAction - строка "Controller/Action"     * @return Array|string Результат компиляции     */    public function compile($what, $withAction) {                if ($withAction &&            strpos($withAction, '/') === false        ) {            return $what;        }                $dataTransportManager = $this->getService('dataTransportManager');        $transport = $dataTransportManager->get('default_input');        list($controller, $action) = explode('/', $withAction);        $controllerManager = $this->getService('controllerManager');        $task = $controllerManager->call(            $controller, $action,            $transport->receiveAll()        );        if ($task) {            $transaction = $task->getTransaction();            if ($transaction) {                $buffer = $transaction->buffer();                if ($buffer) {                    if (!is_array($what))                    {                        return $this->_compileOne($what, $buffer);                    }                    foreach ($what as &$data) {                        $data = $this->_compileOne($data, $buffer);                    }                }            }        }        return $what;    }        /**     * Скомпилировать строку с использованием массива Key=>Value     * @param string $what исходная строка     * @param type $withFields массив Ключ=>Значение     * @return string Результат замены {$Key} на Value     */    protected function _compileOne($what, $withFields) {        if (is_array($withFields)) {             foreach ($withFields as $key => $value) {                if (!is_scalar($value)) {                    continue;                }                $what = str_replace('{$' . $key . '}', $value, $what);            }        }        return $what;    }            /**     * Компилирует переменные, указанные в этой строке формата,      * в реальные значения одноименных полей модели     * @param type $formatStr     * @param type $modelName     * @param type $modelId     */    public function compileWithModel($formatStr, $modelName, $modelId) {            $modelManager = $this->getService('modelManager');        $model = $modelManager->byKey($modelName, $modelId);        if(!empty($model) && !empty($formatStr)) {            $fields = $this->getFieldsForSearch($formatStr);            if(!empty($fields)) {                $modelRaw = $model->raw($fields);                foreach ($modelRaw as $key => $value) {                    if(!is_array($value)) {                        $formatStr = str_replace(                            '{$' . $key . '}',                             $value,                             $formatStr                        );                    }                }            }        }        return $formatStr;    }        /**     * Получает названия полей из строки формата     * @param type $formatStr     * @return array     */    public function getFieldsForSearch($formatStr) {        $fields = array();        preg_match_all('#\{\$([^/}]+)#', $formatStr, $fields);        return $fields[1];    }}