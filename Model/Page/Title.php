<?php

/**
 * @desc Текст страницы
 * Created at: 2012-10-04 09:57:43
 * @author
 * @property integer $id
 * @property integer $active Активность
 * @property integer $weight Порядок примения (обратный порядок)
 * @property integer $City__id Город
 * @property string $controllerAction Обработчик
 * @property string $keywords Обработчик
 * @property string $description Обработчик
 * @property string $pageTitle Заголовок страницы
 * @property string $pattern Паттерн урла страницы
 * @property string $siteTitle Заголовок сайта
 * @property string $pageText Текст страницы
 * @package Vipgeo
 * @category Models
 * @copyright i-complex.ru
 *
 * @Service("pageTitle")
 */
class Page_Title extends Model
{
    /**
     * @deprecated Выпилить. Оставил для совместимости. юзается в 32топ
     *
     * @desc Компиляция заголовка.
     * @param string $field
     * @return string
     */
    public function _compile($field = 'title')
    {
        $vars = array();

        if ($this->sfield($field . 'Action')) {
            $a = explode('/', $this->field($field . 'Action'));
            $params =  $this->getService('request')->params();
            $params['pageTitle'] = $this;
            $task = $this->getService('controllerManager')->call(
                $a [0],
                isset ($a [1]) ? $a [1] : 'index',
                $params
            );

            $vars = $this->variable($task->getTransaction()->buffer());
        }

        $keys = array_keys($vars);
        $vals = array_values($vars);

        foreach ($keys as &$key) {
            $key = '{$' . $key . '}';
        }

        $this->$field = str_replace(
            $keys,
            $vals,
            $this->$field
        );

        return $this;
    }

    /**
     *
     * @deprecated Выпилить. Оставил для совместимости. юзается в 32топ
     *
     * @desc Получение или установка значения.
     * @param string|array $key Ключ или массв пар ключ-значение.
     * @internal param mixed $value [optional] Значение.
     * @return mixed Если передан только ключ, возвращает значение, иначе null.
     */
    public static function variable($key)
    {
        $vars = array();

        if (func_num_args() > 1) {
            $vars [$key] = func_get_arg(1);
        } elseif (is_array($key)) {
            $vars = array_merge(
                $vars,
                $key
            );
        }

        return $vars;
    }

    /**
     * Получить тайтл по городу и урлу
     *
     * @param int $cityId
     * @param string $uri
     * @param null $host
     * @return null
     */
    public function byAddress($cityId = null, $uri = null, $host = null)
    {
        if (!$cityId) {
            $cityId = IcEngine::getServiceLocator()->getService('city')->getCurrent()->key();
        }

        if (!$uri) {
            $uri = IcEngine::getServiceLocator()->getService('request')->uri();
        }

        if (!$host) {
            $host = IcEngine::getServiceLocator()->getService('request')->host();
        }

        $modelManager = $this->getService('modelManager');
        $page = $modelManager->byOptions(
            'Page_Title',
            '::Active',
            array(
                'name' => '::City',
                'id' => array(0, $cityId)
            ),
            array(
                'name' => 'Host',
                'value' => $host
            ),
            array(
                'name' => 'Pattern',
                'value' => $uri
            ),
            array(
                'name' => '::Order_Desc',
                'field' => 'City__id'
            ),
            array(
                'name' => '::Order_Desc',
                'field' => 'weight'
            )
        );
        if (!$page || !$page->siteTitle) {
            if (!$page) {
                $page = $this->createEmpty();
            }
            if (!$page->siteTitle || !$page->pageTitle) {
                $route = $this->getService('router')->getRoute();
                if (isset($route->params) && !empty($route->params['title'])) {
                    $data = $route->params['title'];
                    static $keys = array('siteTitle', 'pageTitle', 'controllerAction');
                    foreach ($keys as $key) {
                        if (!empty($data[$key])) {
                            $page->set($key, $data[$key]);
                        }
                    }
                }
            }
        }
        return $page ? $page->_compile() : null;
    }

    /**
     * По шаблону
     *
     * @param string $pattern
     */
    public function byPattern($pattern)
    {
        if (!$pattern) {
            return;
        }
        $pageTitle = $this->getService('modelManager')->byOptions(
            'Page_Title',
            array(
                'name' => 'Pattern',
                'value' => $pattern
            )
        );
        if ($pageTitle) {
            return $pageTitle;
        }
    }

    /**
     * Компилирует тайтл
     *
     * @return Page_Title
     */
    public function compile()
    {
        if ($this->titleAction &&
            strpos($this->titleAction, '/') !== false
        ) {
            $new_fields = $this->getService('helperCompile')->compile($this->getFields(), $this->titleAction);
            foreach ($new_fields as $key => $value) {
                $this->set($key, $value);
            }
        }
        return $this;
    }

    /**
     * Создать пустую инфо о странице
     *
     * @return Site_Page
     */
    public function createEmpty()
    {
        return $this->getService('modelManager')->create(
            'Page_Title',
            array(
                'pageTitle' => '',
                'siteTitle' => '',
                'controllerAction' => '',
                'pageText' => '',
                'keywords' => '',
                'description' => ''
            )
        );
    }
}