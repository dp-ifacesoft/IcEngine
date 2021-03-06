<?php

/**
 * Класс для работы с HTTP запросом
 *
 * @author morph, goorus, neon
 * @Service("request")
 */
class Request
{
    /**
     * Пустой ip
     */
    const NONE_IP = '0.0.0.0';

    /**
     * Параметры с роута
     *
     * @var array
     */
    public $params = array();

    /**
     * Проверка формата входных данных
     *
     * @return boolean
     */
    public function altFilesFormat()
    {
        if (empty($_FILES)) {
            return false;
        }
        $f = reset($_FILES);
        return is_array($f['name']);
    }

    /**
     * Получить текущий хост
     *
     * @return Ambigous <string, NULL>
     */
    public function host()
    {
        return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
    }

    /**
     * Получение параметра GET.
     *
     * @param string $name Имя параметра
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function get($name, $default = false)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * IP источника запроса
     *
     * @return string
     */
    public function ip()
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return isset($_SERVER['REMOTE_ADDR']) ?
            $_SERVER['REMOTE_ADDR'] : self::NONE_IP;
    }

    /**
     * Проверить пришел ли запрос через xmlhttprequest
     *
     * @return boolean
     */
    public function isAjax()
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        );
    }

    /**
     * Проверяет, что скрипт был вызван через консоль.
     *
     * @return boolean true, если скрипт был вызван из командной строки,
     * иначе - false.
     */
    public function isConsole()
    {
        return isset($_SERVER['argv'], $_SERVER['argc']);
    }

    /**
     * Проверяет, передены ли файлы от пользователя.
     *
     * @return boolean
     */
    public function isFiles()
    {
        return !empty($_FILES);
    }

    /**
     * Проверяет, переданы ли GET параметры.
     *
     * @return boolean
     */
    public function isGet()
    {
        return !empty($_GET) || (
            isset($_SERVER['REQUEST_METHOD']) &&
            $_SERVER['REQUEST_METHOD'] == 'GET'
        );
    }

    /**
     * Проверяет, был ли это запрос через JsHttpRequest
     *
     * @return boolean
     */
    public function isJsHttpRequest()
    {
        global $JsHttpRequest_Active;
        return $this->isPost() && !empty($JsHttpRequest_Active);
    }

    /**
     * Проверяет, что это был POST запрос
     *
     * @return boolean
     */
    public function isPost()
    {
        return (
            isset($_SERVER['REQUEST_METHOD']) &&
            $_SERVER['REQUEST_METHOD'] == 'POST'
        );
    }

    /**
     * Проверяет пришел ли запрос через ssi
     *
     * @return bolean
     */
    public function isSsi()
    {
        return (
            isset($_SERVER['REQUEST_METHOD']) &&
            $_SERVER['REQUEST_METHOD'] == 'ssi'
        );
    }

    /**
     * Получение или установка параметра.
     *
     * @param string $key Название параметра.
     * @param string $value [optional] Значение.
     * Если передано значение, до оно будет установлено.
     * @return string|null Если указано только название параметра, то
     * возращается его значение.
     */
    public function param($key)
    {
        if (func_num_args() > 1) {
            $this->params[$key] = func_get_arg(1);
        } else {
            return isset($this->params[$key]) ? $this->params[$key] : null;
        }
    }

    /**
     * Возвращает все параметры адресной строки.
     *
     * Это не GET параметры, а параметры, определяемые роутом.
     * @return array
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * Распарсить содержимое php://input для REST-запросов и выдать ассоциативный массив пар ['key'=>'value']
     *
     * @return array
     */
    public function parsePhpInput()
    {
        $data = (file_get_contents('php://input'));
        $dataParsed = [];
        parse_str($data, $dataParsed);
        return $dataParsed;
    }

    /**
     * Получение параметра POST.
     *
     * @param string $name Имя параметра
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function post($name, $default = false)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }
    
    public function time($float=false)
    {
        if ($float) {
            $requestTime = $_SERVER['REQUEST_TIME_FLOAT'];
        } else {
            $requestTime = $_SERVER['REQUEST_TIME'];
        }
        return $requestTime;
    }

    /**
     * Получить файл из запроса
     *
     * @param string $name Имя поля
     * @return PostedFile|false
     */
    public function file($name)
    {
        if (isset($_FILES[$name]) && !empty($_FILES[$name]['name'])) {
            return new Request_File($_FILES[$name]);
        } else {
            return false;
        }
    }

    /**
     * Возвращает объект переданного файла.
     *
     * @param integer $index Индекс.
     * @return Request_File Переданный файл.
     *        Если файлов меньше, чем указанный индекс - null.
     */
    public function fileByIndex($index)
    {
        $files = array_values($_FILES);
        if (!isset($files[$index])) {
            $f = '@file:' . $index;
            if (isset($_POST[$f])) {
                return new Request_File_Test($_POST[$f]);
            }
            if (isset($_POST['params'], $_POST['params'][$f])) {
                return new Request_File_Test($_POST['params'][$f]);
            }
            return null;
        }
        if (is_array($files[$index]['name'])) {
            $file = array();
            foreach ($files[$index] as $field => $values) {
                $file[$field] = reset($values);
            }
            return new Request_File($file);
        }
        return new Request_File($files[$index]);
    }

    /**
     * Возвращает массив объектов переданных файлов.
     *
     * @return array Request_File
     */
    public static function files()
    {
        $result = array();
        foreach ($_FILES as $name => $file) {
            $result[$name] = new Request_File($file);
        }
        return $result;
    }

    /**
     * Возвращает часть адреса без параметров GET.
     *
     * @return string Часть URI до знака "?"
     */
    public function uri($withoutGet = true)
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return null;
        }
        $url = $_SERVER['REQUEST_URI'];
        if ($withoutGet) {
            $p = strpos($url, '?');
            if ($p !== false) {
                return substr($url, 0, $p);
            }
        }
        return $url;
    }

    /**
     * Возвращает часть запроса GET
     *
     * @return string Часть URI после знака "?"
     */
    public function stringGet()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return '';
        }
        $url = $_SERVER['REQUEST_URI'];
        $p = strpos($url, '?');
        if ($p !== false) {
            return substr($url, $p + 1);
        }
        return '';
    }

    /**
     * Получить поддомен
     *
     * @return string
     */
    public function subdomain($default = null)
    {
        $host = $this->host();
        $helperUri = IcEngine::serviceLocator()->getService('helperUri');
        $main = $helperUri->mainDomain();
        $subdomain = trim(str_replace($main, '', $host), '.');
        return $subdomain ? : $default;
    }

    /**
     * Получить входные данные из всех возможных источников в зависимости от текущего HTTP-метода
     *
     * @param array $input Ассоциативный массив всего, что содержится в Data_Transport
     *
     * @return array
     */
    public function receiveAllFromInput(array $input)
    {
        $id = !empty($input['id']) ? (int) $input['id'] : null;
        $httpMethod = $this->requestMethod();
        switch ($httpMethod) {
            case 'DELETE':
            case 'GET':
                return [
                    'id' => $id,
                ];
            case 'PATCH':
            case 'POST':
            case 'PUT':
                return $this->parsePhpInput();
            default:
                return array_merge(
                    [
                        'id' => $id,
                    ],
                    $this->parsePhpInput()
                );
        }
    }

    /**
     * Получить реферер
     *
     * @return string
     */
    public function referer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
     * Получить метод пересылки
     *
     * @return string
     */
    public function requestMethod()
    {
        return isset($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    /**
     * Возвращает название сервера.
     * В зависимости от настроек nginx может вернуть "*.server.com"
     *
     * @return string
     */
    public function server()
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    }

    /**
     * Получить id сессии
     *
     * @throws ErrorException
     * @return string
     */
    public function sessionId()
    {
        $serviceLocator = IcEngine::serviceLocator();
        $sessionManager = $serviceLocator->getService('sessionManager');
        $sessionManager->init();
        
        if (!isset($_SESSION)) {
            session_start();
        }
        return session_id();
    }
}