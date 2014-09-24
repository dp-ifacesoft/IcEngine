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
     * Названия валидаторов для HTTP-метода GET
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
     * Названия валидаторов для HTTP-метода POST
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
     * Получить требуемые валидаторы для HTTP-метода и текущего значения $this->_action
     *
     * Метод объявлен финальным, чтобы не было возможности избежать начального набора валидаторов в сервисах-потомках.
     *
     * @return array
     */
    protected final function _getValidators()
    {
        $action = $this->getAction();
        $httpMethod = $this->_getHttpMethodPrefix();
        $validators = array_merge(
            [
                'Rest_Api_Action_Correct',
            ],
            $this->_getValidatorsFor($httpMethod),
            $this->_getValidatorsFor($action)
        );
        return $validators;
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
     * Получить URL для запроса данных модели
     *
     * @param int    $id            ID модели
     * @param string $viewRender    Название требуемого View_Render'а: 'json' или 'xml'
     *
     * @return string
     */
    protected function _modelGetUri($id, $viewRender = 'json')
    {
        return '/REST/v1/' . $this->getModelName() . '/get/byId/' . $id . '.' . $viewRender;
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
     * @return $this
     * @throws Exception в случае неудачной валидации
     */
    protected final function _validateMe()
    {
        $validators = $this->_getValidators();
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
                ->_validateMe()
                ->$action()
            ;
        }
        $this->_raiseHttpStatus(500);
        throw new Exception(__METHOD__ . ' requires an action name to be set');
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
     * Установить целевой метод для вызова
     *
     * Например, для HTTP-метода POST и экшена 'something' будет возвращено значение 'postSomething',
     * для HTTP-метода GET и экшена 'current' будет возвращено значение 'getCurrent'.
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