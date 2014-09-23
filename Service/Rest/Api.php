<?php
/**
 * Абстрактный сервис REST API
 *
 * @author LiverEnemy
 */

abstract class Service_Rest_Api extends Service_Abstract
{
    /**
     * @var string Имя целевого метода для вызова
     */
    protected $_action;

    /**
     * Допустимые HTTP-методы для REST-сервиса.
     *
     * Если определенный HTTP-метод не реализован в сервисе, не стоит включать его в данный массив,
     * и тогда обращение к сервису с недозволенным методом
     * будет вызывать ошибку 405 (метод для ресурса не поддерживается),
     * с обязательным возвратом клиенту списка поддерживаемых методов из данного поля.
     *
     * @var array
     */
    protected $_allowMethods = ['GET'];

    /**
     * Имя модели, с которой работает данный конкретный сервис
     *
     * @var string
     */
    protected $_modelName;

    /**
     * Массив полей модели, с которыми имеет право работать сервис
     *
     * Если данное свойство установлено внутри класса сервиса, соответствующий конфиг будет игнорироваться.
     *
     * @var array
     */
    protected $_modelFields = [];

    /**
     * Методы, запрещенные к вызову со стороны клиента (через контроллер)
     *
     * @var array
     */
    public static $prohibitedActions = [
        'getAction',
        'getModelFields',
        'getModelName',
        'getRequestData',
    ];

    /**
     * Массив данных, относящихся к текущему запросу
     *
     * Данные текущего запроса лежат в отдельном поле, чтобы к ним могли получить доступ сторонние валидаторы,
     * если им задан в качестве проверяемых данных экземпляр текущего сервиса REST API.
     *
     * @var array
     */
    protected $_requestData = [];

    /**
     * Названия валидаторов для метода get()
     *
     * @var array
     */
    protected $_validatorsGet = [
        'Rest_Api_Allows_Http_Method',
        'Rest_Api_Model_Not_Empty',
        'Rest_Api_Model_Fields_Not_Empty',
        'Rest_Api_Action_Exists',
    ];

    /**
     * Названия валидаторов для метода post()
     *
     * @var array
     */
    protected $_validatorsPost = [
        'Rest_Api_Allows_Http_Method',
        'Rest_Api_Model_Not_Empty',
        'Rest_Api_Action_Exists',
    ];

    /**
     * Отфильтровать запись по разрешенным полям
     *
     * Допустим, у нас есть запись вида ['id'=>1, 'name'=>'Валера', 'status'=>'ВедущийПрограммист'],
     * а отдавать из сервиса нам разрешено только поля ['id', 'name'].
     * Данный метод вернет после фильтрации исходной записи массив ['id'=>1,'name'=>'Валера'].
     *
     * @param array $row Исходная запись
     *
     * @return array
     */
    protected function _filterFields(array $row)
    {
        $modelFields = $this->getModelFields();
        $result = [];
        foreach ($row as $name => $value)
        {
            if (in_array($name, $modelFields))
            {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    /**
     * Получить имена валидаторов для определенного метода
     *
     * @param $method Название метода, для которого предназначены валидаторы
     *
     * @return array
     */
    protected function _getValidatorsFor($method)
    {
        $validators = '_validators' . ucfirst($method);
        if (property_exists($this, $validators))
        {
            return $this->$validators;
        }
        return [];
    }

    /**
     * Проверить, всё ли в порядке в настройках сервиса
     *
     * @return bool
     * @throws Exception
     */
    protected function _isOk()
    {
        /**
         * Проверим, установлено ли значение поля $_modelName у сервиса
         */
        $modelName = $this->getModelName();
        if (empty($modelName))
        {
            $this->_raiseHttpStatus(404);
            throw new Exception(__METHOD__ . ' requires a model name of a ' . get_class($this) . ' to be set');
        }
        /**
         * Проверим, имеет ли право сервис работать с этой моделью (непустой массив имен полей модели в конфиге)
         */
        $modelFields = $this->getModelFields();
        if (empty($modelFields))
        {
            $this->_raiseHttpStatus(500);
            throw new Exception(__METHOD__ . ' requires a model name to be in ' . get_class($this) . ' config');
        }
        return TRUE;
    }

    /**
     * Получить URL для запроса данных модели
     *
     * @param int    $id            ID модели
     * @param string $viewRender    Название требуемого View_Render'а: 'json' или 'xml'
     *
     * @return string
     */
    protected function _modelGetUri($id, $viewRender = 'json')
    {
        return '/REST/v1/' . $this->getModelName() . '/get/' . $id . '.' . $viewRender;
    }

    /**
     * Выдать клиенту HTTP-статус
     *
     * @param int   $code   Код HTTP-статуса, который требуется вызвать
     * @param array $params Дополнительные параметры в виде ассоциативного массива (например, Location для 301)
     *
     * @return $this
     */
    protected function _raiseHttpStatus($code, array $params = [])
    {
        /** @var Service_Http_Header $serviceHttpHeader */
        $serviceHttpHeader = $this->getService('serviceHttpHeader');
        $serviceHttpHeader->sendHeaderHttpStatus($code, TRUE, $params);
        return $this;
    }

    /**
     * Провести самовалидацию для определенного метода
     *
     * Метод объявлен финальным, чтобы в дочерних классах нельзя было избежать
     * обязательной валидации на корректность целевого метода.
     *
     * @param array $methods Названия методов, для которых требуется провести самовалидацию
     *
     * @return $this
     * @throws Exception в случае неудачной валидации
     */
    protected final function _validateMeFor(array $methods)
    {
        $validators = [
            'Rest_Api_Action_Correct',
        ];
        foreach ($methods as $methodName)
        {
            if (strpos($methodName, '::'))
            {
                $method = substr($methodName, strpos($methodName, '::') + 2, strlen($methodName));
            }
            else
            {
                $method = $methodName;
            }
            $validators = array_merge($validators, $this->_getValidatorsFor($method));
        }
        if (empty($validators))
        {
            return $this;
        }
        /** @var Validator_Pool_Rest_Api $validatorPool */
        $validatorPool = $this->getService('validatorPoolRestApi');
        $validatorPool
            ->setData($this)
            ->setValidators($validators)
            ->validate();
        if (!$validatorPool->isOk())
        {
            $error = $validatorPool->error();
            $error->processError();
            throw new Exception($error->errorMessage());
        }
        return $this;
    }

    /**
     * Получить список поддерживаемых сервисом HTTP-методов
     *
     * @return array
     */
    public function allowMethods()
    {
        return $this->_allowMethods;
    }

    /**
     * Проверить, поддерживается ли сервисом указанный HTTP-метод.
     *
     * Мало указать поддержку метода в специальном поле $this->_allowMethods -
     * надо еще и реализовать соответствующую функцию-метод в классе REST API.
     *
     * @param $methodName Название проверяемого HTTP-метода
     *
     * @return bool
     */
    public function allowsMethod($methodName)
    {
        return in_array($methodName, $this->allowMethods());
    }

    /**
     * Запустить целевой экшен
     *
     *
     * Единая точка входа для всех запросов.
     *
     * Метод объявлен финальным, чтобы в дочерних классах нельзя было переопределить его
     * и избежать таким образом обязательной валидации критически важных компонентов.
     *
     * Подходящий метод будет выбираться из значения $this->getAction(), а возвращаться будет результат его выполнения.
     *
     * Предполагается, что конкретные методы в реализациях будут protected,
     * чтобы их не запускали непосредственно из контроллера.
     *
     * Это даст возможность вернуть соответствующий ситуации HTTP-заголовок и еще много вкусных плюшек, таких, как:
     *  -   Валидация прав доступа. Например, мы хотим создать новый комментарий на сайте, но сервер должен проверить,
     *      не забанены ли мы на сайте.
     *  -   Валидация на подозрительную активность - не слишком ли много от клиента запросов в единицу времени.
     *  -   Проверка на соблюдение каких-то еще условий. Вдруг, например, сайт находится на техобслуживании (выкладка)
     *      и нам надо попросить контент-менеджера подождать пару минут, чтобы не потерялись данные.
     *
     * @return array|NULL   Массив полей требуемой модели (если она есть) либо NULL в случае ошибки
     * @throws Exception    в случае, если не установлено имя модели, которую требуется получить
     */
    public final function call()
    {
        $action = $this->getAction();
        if (!empty($action))
        {
            return $this
                ->_validateMeFor([
                    $this->_getHttpMethodPrefix(),
                    $action,
                ])
                ->$action()
            ;
        }
        $this->_raiseHttpStatus(500);
        throw new Exception(__METHOD__ . ' requires an action name in data array to be set');
    }

    /**
     * Получить название текущего метода
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Получить текущий HTTP-метод в нижнем регистре
     *
     * Например, для HTTP-метода GET возвращаемым значением будет 'get',
     * для запроса POST вернется значение 'post', и так далее.
     *
     * Метод требуется для конструирования названия $action.
     * Например, если в REST-контроллер пришла переменная $action, равная 'current', а HTTP-запрос делается по GET,
     * метод $this->setAction() получит отсюда префикс HTTP-метода, равный 'get',
     * и сконструирует название целевого метода как 'getCurrent'.
     *
     * @return string
     */
    protected function _getHttpMethodPrefix()
    {
        /** @var Request $request */
        $request = $this->getService('request');
        $httpMethod = $request->requestMethod();
        return strtolower($httpMethod);
    }

    /**
     * Получить массив полей модели, с которыми имеет право работать сервис
     *
     * @return array
     */
    public function getModelFields()
    {
        $modelName = $this->getModelName();
        if (empty($this->_modelFields))
        {
            $config = $this->config();
            if ($config->offsetExists($modelName))
            {
                $this->_modelFields = $config->offsetGet($modelName)->__toArray();
            }
        }
        return $this->_modelFields;
    }

    /**
     * Получить название модели, с которой работает данный сервис
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->_modelName;
    }

    /**
     * Получить данные, относящиеся к текущему запросу
     *
     * @return array
     */
    public function getRequestData()
    {
        return $this->_requestData;
    }

    /**
     * Единая точка входа для POST-запросов.
     *
     * Подходящий метод будет выбираться из значения $data['action'], а возвращаться будет результат его выполнения.
     *
     * Предполагается, что конкретные методы в реализациях будут protected,
     * чтобы их не запускали непосредственно из контроллера.
     *
     * Это даст возможность вернуть соответствующий ситуации HTTP-заголовок и еще много вкусных плюшек, таких, как:
     *  -   Валидация прав доступа. Например, мы хотим создать новый комментарий на сайте, но сервер должен проверить,
     *      не забанены ли мы на сайте.
     *  -   Валидация на подозрительную активность - не слишком ли много от клиента запросов в единицу времени.
     *  -   Проверка на соблюдение каких-то еще условий. Вдруг, например, сайт находится на техобслуживании (выкладка)
     *      и нам надо попросить контент-менеджера подождать пару минут, чтобы не потерялись данные.
     *
     * @internal param array $data   Ассоциативный массив данных, включая название требуемого метода
     *
     * @return array        Результат выполнения конкретного метода
     * @throws Exception    В случае отсутствия значения action в аргументе
     *                      или если указанный в action метод не существует
     */
    public function post()
    {
        $data = $this->getRequestData();
        if (!empty($data['action']))
        {
            $actionName = 'post' . ucfirst($data['action']);
            return $this
                ->_validateMeFor([
                    __METHOD__,
                    $actionName,
                ])
                ->$actionName()
            ;
        }
        $this->_raiseHttpStatus(500);
        throw new Exception(__METHOD__ . ' requires an action name in data array to be set');
//        if (!is_array($data) || empty($data['action']))
//        {
//            $this->_raiseHttpStatus(500);
//            throw new Exception(__METHOD__ . ' requires an argument to be an array and to content an \'action\' item');
//        }
//        /**
//         * Если указанный параметр action не ссылается на существующий в данном сервисе метод,
//         * отправляем клиенту заголовок 406 Not Acceptable:
//         * "запрошенный URI не может удовлетворить переданным в заголовке характеристикам".
//         */
//        if (!method_exists($this, $actionName))
//        {
//            $this->_raiseHttpStatus(406);
//            throw new Exception(__METHOD__ . ' requires an action name to be a valid name of current REST API method');
//        }

    }

    public function processNotAllowedMethod($methodName)
    {
        if (!$this->allowsMethod($methodName))
        {
            /** @var Service_Http_Header $httpHeader */
            $httpHeader = $this->getService('serviceHttpHeader');
            $httpHeader->sendHeaderHttpStatus(405, true, [
                'Allow' => implode(',', $this->allowMethods()),
            ]);
        }
    }

    /**
     * Установить целевой метод для вызова
     *
     * @param string $name
     *
     * @return $this
     */
    public function setAction($name)
    {
        $this->_action = $this->_getHttpMethodPrefix() . ucfirst($name);
        return $this;
    }

    /**
     * Установить данные, относящиеся к текущему запросу
     *
     * @param array $data Устанавливаемые данные
     * @return $this
     */
    public function setRequestData(array $data)
    {
        $this->_requestData = $data;
        return $this;
    }
}