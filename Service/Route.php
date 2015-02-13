<?php
/**
 * Сервис для работы с роутами
 *
 * @author LiverEnemy
 * @Service("serviceRoute")
 */

class Service_Route
{
    /**
     * Проверить, удовлетворяет ли маршрут указанным параметрам
     *
     * @param array $route  Проверяемый маршрут
     * @param array $params Параметры, которые планируется подставлять в машрут
     *
     * @return bool
     */
    protected function _checkRouteParams(array $route, array $params = [])
    {
        if (!isset($route['components'])) {
            return empty($params);
        }
        /**
         * @var string $name Имя переменной - компонента маршрута
         * @var array  $data Массив данных о компоненте маршрута ($data['optional'] означает необязательность)
         */
        foreach ($route['components'] as $name => $data) {
            if (!isset($params[$name])) {
                if (!empty($data['optional'])) {
                    continue;
                }
                return FALSE;
            }
            if (!empty($data['pattern']) && !preg_match('#^' . $data['pattern'] . '$#', $params[$name])) {
                return FALSE;
            }
            unset($params[$name]);
        }
        return !count($params); // если все параметры подошли и были вычеркнуты, роут нам подходит
    }

    /**
     * Скомпилировать маску в URL, используя предоставленные параметры
     *
     * Метод не возвращает ошибок в случае, если переданы не все обязательные параметры.
     * Метод заменяет все неупомянутые в параметрах {$...}-подобные участки урла на пустые строки.
     *
     * @param array  $route  Маршрут, чей URL компилируется
     * @param array  $params Параметры для компиляции URL-а маршрута
     *
     * @return string
     * @throws Exception В случае не-строкового URL, поданного на вход метода
     */
    public function _compileUrl(array $route, array $params = [])
    {
        if (!isset($route[0]) || !is_string($route[0])) {
            throw new Exception(__METHOD__ . ' requires a route URL to be a string');
        }
        $url = $route[0];
        if (!empty($route['components']) && is_array($route['components'])) {
            foreach ($route['components'] as $name => $data) {
                $value = !empty($params[$name]) ? $params[$name] : '';
                $url = $this->_replacePart($name, $value, $url);
            }
        }
        return $url;
    }

    /**
     * Извлечь требуемые для маршрута параметры из $additionalParams
     *
     * @param string $controllerAction  Котроллер-экшен
     * @param array  $data              Данные для извлечения
     *
     * @return array
     * @throws Exception В случае не-строкового $controllerAction
     */
    protected function _extractRequiredParams($controllerAction, array $data = [])
    {
        list($class, $method) = $this->_resolveMethodName($controllerAction);
        $routeParamExtractorManager = App::routeParamExtractorManager();
        $routeParamExtractor = $routeParamExtractorManager->get($class);
        if (!$routeParamExtractor) {
            return [];
        }
        return $routeParamExtractor->call($method, $data);
    }

    /**
     * Получить все роуты указанного контроллер-экшена
     *
     * @param string $controllerAction
     *
     * @return array
     * @throws Exception В случае несуществующего $controllerAction
     */
    protected function _getRoutes($controllerAction)
    {
        list($class, $method) = $this->_resolveMethodName($controllerAction);
        $helperAnnotation = App::helperAnnotation();
        $methodAnnotations = $helperAnnotation->getAnnotation($class)->getMethod($method);
        if (!is_array($methodAnnotations) || empty($methodAnnotations['Route'])) {
            return [];
        }
        return $methodAnnotations['Route'];
    }
    
    /**
     * Получить текущий controllerAction
     * 
     * @return string
     */
    public function getCurrentAction()
    {
        $route = App::router()->getRoute();
        return $route['actions'][0];
    }

    /**
     * Заменить переменную в шаблоне урла на значение этой переменной
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    protected function _replacePart($search, $replace, $subject)
    {
        return str_replace('{$' . $search . '}', $replace, $subject);
    }

    /**
     * Получить название класса и метода контроллер-экшена из строки вида 'controller/action'
     *
     * @param string $controllerAction
     *
     * @return array Массив, где нулевой элемент - имя класса контроллера, а первый элемент - имя метода экшена
     * @throws Exception в случае, если указанный класс или метод не существует
     */
    protected function _resolveMethodName($controllerAction)
    {
        if (!is_string($controllerAction)) {
            throw new Exception(__METHOD__ . ' requires a controllerAction param to be string');
        }
        $controllerAndMethod = explode('/', $controllerAction);
        $class  = 'Controller_' . $controllerAndMethod[0];
        if (!class_exists($class)) {
            throw new Exception(__METHOD__ . ' requires a controller class "' . $class . '" to exist');
        }
        $method = !empty($controllerAndMethod[1]) ? $controllerAndMethod[1] : 'index';
        if (!method_exists($class, $method)) {
            throw new Exception(__METHOD__ . ' requires a controller action "' . $method . '" to exist');
        }
        return [$class, $method];
    }

    /**
     * Создать URL для контроллер-экшена с указанными параметрами
     *
     * Например, есть у нас контроллер Controller_Clinic_View, а у него - экшен index.
     * К данному контроллеру имеется два маршрута - "/{$mode}/{$clinicId}/{$page}" с необязательным {$page}
     * и "/{$mode}/{$clinicId}/comment/{$commentId}". В обоих маршрутах значения $mode и $clinicId
     * сильно зависят от клиники, к странице которой надо получить URL. Поэтому есть два варианта создания урла:
     *  - $serviceRoute->createUrl(
     *      'Clinic_View',
     *      [
     *          'mode'      => 'nc', //где mode у каждой клиники в разном формате
     *          'clinicId'  => 923,
     *      ]
     *  );
     * либо
     *  - $serviceRoute->createUrl(
     *      'Clinic_View',
     *      [],
     *      [
     *          'Clinic' => $clinic, // объект клиники, для которой требуется маршрут
     *      ]
     *  );
     * Во втором случае необходимо в целевом контроллере реализовать:
     *  - Интерфейс Interface_Route_Compatible,
     *  - Удовлетворяющий интерфейсу публичный статический метод getRouteParams($action, array $from = []).
     *      Параметр $from получит копию $data из данного метода,
     *      а в $action упадет название целевого метода контроллера, к которому нам требуется маршрут.
     *      Метод getRouteParams должен вернуть ассоциативный массив параметров, требуемых для маршрута.
     *      Возвращенный массив будет слит с указанными $paramsRequired,
     *      результат упадет в $paramsRequired, но элементы с одинаковыми индексами в $paramsRequired
     *      не будут заменены на вычисленные в getRouteParams() - они будут лишь дополнены результатами
     *      в случае их отсутствия.
     *
     * @param string $controllerAction  Контроллер-экшен в формате 'Name_Of_Controller/actionName'
     * @param array  $params            Ассоциативный массив параметров, входящих в маршрут
     * @param array  $data              Ассоциативный массив данных, пригодных для заполнения недостающих
     *                                  элементов $paramsRequired
     *
     * @return string
     * @throws Exception В случае не-строкового $controllerAction или в случае отсутствия подходящего роута
     */
    public function createUrl($controllerAction, array $params = [], array $data = [])
    {
        $routes = $this->_getRoutes($controllerAction);
        if (!is_array($routes) || !count($routes)) {
            throw new Exception(__METHOD__ . ' requires a controllerAction to have at least one route');
        }
        $extraRequired = $this->_extractRequiredParams($controllerAction, $data);
        $params = array_merge($extraRequired, $params);
        foreach ($routes as $route) {
            if ($this->_checkRouteParams($route, $params)) {
                return $this->_compileUrl($route, $params);
            }
        }
        throw new Exception(__METHOD__ . ' has not found any routes for your params');
    }

    /**
     * Скомпилировать URL из шаблона и массива значений переменных этого шаблона
     *
     * @param string $template
     * @param array $values
     * @return string
     * @throws Exception
     */
    public function compile($template, array $values = [])
    {
        if (!is_string($template)) {
            throw new Exception(__METHOD__ . ' requires a provided template to be a string');
        }
        foreach ($values as $key => $value) {
            $template = $this->_replacePart($key, $value, $template);
        }
        return $template;
    }

    /**
     * Получить ассоциативный массив имен классов полей выбора для компонентов маршрута
     *
     * @param array $route Массив данных о маршруте
     * @return array|null  Ассоциативный массив имен Html_Form_Field'ов для компонентов маршрута или NULL
     */
    public function getFormFieldNames(array $route = [])
    {
        if (empty($route['patterns']) || !is_array($route['patterns'])) {
            return NULL;
        }
        $result = [];
        foreach ($route['patterns'] as $name => $data) {
            if (!empty($data['formField'])) {
                $result[$name] = $data['formField'];
            }
        }
        return $result;
    }
} 