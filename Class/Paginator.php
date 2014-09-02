<?php

/**
 * Объект для хранения списка страниц.
 *
 * @author goorus, neon
 * @Service("paginator", disableConstruct=true)
 */
class Paginator
{
    /**
     * Флаг означает, что ссылки нам нужны без всяких ?&page
     *
     * @var bool
     */
    protected $notGet = false;

    /**
     * Общее количество элементов
     *
     * @var integer
     */
    protected $total;

    /**
     * Ссылка на страницу.
     * Если на задана, будешь использован адрес из запроса.
     *
     * @var string
     */
    protected $href;

    /**
     * Текущая страница
     *
     * @var integer
     */
    protected $page;

    /**
     * Количество страниц
     *
     * @var integer
     */
    protected $pageCount;

    /**
     * Количество элементов на странице
     *
     * @var integer
     */
    protected $perPage = 30;

    /**
     * Сформированные для вывода номера страниц
     * array(
     *      'href'	=> ссылка на страница
     * 		'title'	=> номер страницы или многоточие
     * )
     *
     * @var array
     */
    protected $pages;

    /**
     * Предыдущая страница
     *
     * @var array
     */
    protected $prev;

    /**
     * Предыдущая страница от выбранной
     *
     * @var array
     */
    protected $prevPage;

    /**
     * Следующая страница
     *
     * @var array
     */
    protected $next;

    /**
     * Следующая страница от выбранной
     *
     * @var array
     */
    protected $nextPage;

    /**
     * Конструктор
     *
     * @param integer $page     Текущая страница
     * @param integer $perPage  Количество элементов на странице
     * @param integer $total    Полное количество элементов
     * @param boolean $notGet   ЧПУ стиль
     */
    public function __construct($page, $perPage = 30,
                                $total = 0, $notGet = false)
    {
        $prevPage =
            $page > 1
                ? ($page - 1)
                : 1
        ;
        $this
            ->setNotGet($notGet)
            ->setPage($page)
            ->setPerPage($perPage)
            ->setPrevPage($prevPage)
            ->initHref()
            ->setTotal($total);
        $pageCount = $this->getPageCount();
        $nextPage =
            $page < $pageCount
            ? ($page + 1)
            : $pageCount
        ;
        $this->setNextPage($nextPage);
    }

    /**
     * Получить значение private- или protected-поля класса
     *
     * Автор осознавал, что магические методы работают медленно, но отрефакторить пагинатор СРАЗУ на всех проектах
     * не реально; реально лишь создать обработчики-сеттеры на свойства класса, чтобы все свойства обновлялись
     * своевременно.
     *
     * @param  string $property Имя поля
     *
     * @return mixed            Значение поля
     * @throws Exception        В случае, если запрошено несуществующее поле
     */
    public function __get($property)
    {
        $method = "get{$property}";
        if (method_exists($this, $method))
        {
            return $this->$method();
        }
        throw new Exception($property . ' is not set in ' . __CLASS__);
    }

    /**
     * Присвоить значение private- или protected-свойству класса
     *
     * Автор осознавал, что магические методы работают медленно, но отрефакторить пагинатор СРАЗУ на всех проектах
     * не реально; реально лишь создать обработчики-сеттеры на свойства класса, чтобы все свойства обновлялись
     * своевременно.
     *
     * @param string $property  Название свойства
     * @param Mixed  $value     Присваиваемое значение
     *
     * @return $this|Mixed
     */
    public function __set($property, $value)
    {
        $method = "set{$property}";
        if (method_exists($this, $method))
        {
            return $this->$method($value);
        }
        $this->$property = $value;
        return $this;
    }

    /**
     * Посчитать количество страниц пагинатора
     *
     * @return int
     */
    protected function _calcPageCount()
    {
        $total = $this->getTotal();

        if ($this->perPage > 0) {
            if ($total) {
                return ceil($total / $this->perPage);
            } elseif (isset($this->fullCount)) {
                return ceil($this->fullCount / $this->perPage);
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    }

    /**
     * Установить количество страниц
     *
     * Автор понимает, что нельзя такой метод давать в публичный доступ. Однако, в коде каких-то из проектов
     * может использоваться $paginator->pageCount=..., что вызовет через магию этот метод.
     * Впилено публичным, чтобы не сломать говнокод на тех проектах, где это используется
     * присвоение $paginator->pageCount=....
     *
     * @param int $count Новое количество элементов на страницу
     *
     * @return $this
     */
    public function setPageCount($count)
    {
        $this->pageCount = (int) $count;
        return $this;
    }

    /**
     * Установить новые страницы пагинатора
     *
     * @param $pages
     *
     * @return $this
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * Получить базовый урл пагинатора (без параметра $page)
     *
     * @return string
     */
    public function getHref()
    {
        if (!$this->href)
        {
            $this->initHref();
        }
        return $this->href;
    }

    /**
     * Получить текущее значение свойства next
     *
     * @return array
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Получить текущее значение свойства nextPage
     *
     * @return array|float|int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * Получить текущее значение Paginator::$notGet
     *
     * @return bool
     */
    public function getNotGet()
    {
        return $this->notGet;
    }

    /**
     * Получить значение текущей страницы
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Получить количество страниц пагинатора
     *
     * @return float|int
     */
    public function getPageCount()
    {
        if (!$this->pageCount)
        {
            $this->refreshPageCount();
        }
        return $this->pageCount;
    }

    /**
     * Получить страницы пагинатора
     *
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Получить количество элементов на страницу
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Получить значение свойства prev
     *
     * @return array
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * Получить значение свойства prevPage
     *
     * @return array|int
     */
    public function getPrevPage()
    {
        return $this->prevPage;
    }

    /**
     * Получить количество записей пагинатора
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Возвращает новый экземпляр
     *
     * @param integer $page Текущая страница
     * @param integer $perPage Количество элементов на странице
     * @param integer $total Полное количество элементов
     * @param boolean $notGet ЧПУ стиль
     *
     * @return $this
     */
    public function newInstance($page, $perPage = 30,
                                $total = 0, $notGet = false)
    {
        return new self($page, $perPage, $total, $notGet);
    }

    /**
     * Инициализировать базовый урл пагинатора
     *
     * @return $this
     */
    public function initHref()
    {
        $locator = IcEngine::serviceLocator();
        /** @var Request $request */
        $request = $locator->getService('request');
        // Удаление из запроса GET параметра page
        $p = 'page';
        $page = $this->getPage();
        $href = preg_replace(
            "/((?:\?|&)$p(?:\=[^&]*)?$)+|((?<=[?&])$p" .
            "(?:\=[^&]*)?&)+|((?<=[?&])$p" .
            "(?:\=[^&]*)?(?=&|$))+|(\?$p(?:\=[^&]*)?".
            "(?=(&$p(?:\=[^&]*)?)+))+/",
            '',
            isset($this->href) ? $this->href : $request->uri(false)
        );
        /**
         * Для ссылок вида $page/, тоже учтём
         */
        if (!$this->notGet) {
            if (strpos($href, '?') === false) {
                $href .= '?page=';
            } else {
                $href .= '&page=';
            }
        } elseif ($page > 1) {
            $href = substr(
                $href, 0, (int) (strlen((string) $page) + 1) * -1
            );
        }
        $this->setHref($href);
        return $this;
    }

    /**
     * Заполнение массива страниц со ссылками.
     *
     * @return $this
     */
    public function buildPages()
    {
        $page = $this->getPage();
        $pages = array();
        $pagesCount = $this->pagesCount();
        if ($pagesCount <= 1) {
            return $this;
        }
        $notGet = $this->getNotGet();
        $this->refreshPageCount();
        $halfPage = round($pagesCount / 2);
        $spaced = false;
        $href = $this->getHref();
        for ($i = 1; $i <= $pagesCount; $i++) {
            if ($i <= 3 ||							// первые 3 страницы
                ($pagesCount - $i) < 3 ||			// последние 3 страницы
                abs($halfPage - $i) < 3 ||			// середина
                abs($page - $i) < 3			// возле текущей
            ) {
                $pageHref = $href;
                if (!($i == 1 && $notGet)) {
                    $pageHref .= $i;
                }
                if ($notGet && $i != 1) {
                    $pageHref .= '/';
                }
                // Ссылка с номером страницы
                $page = array(
                    'href'	    => $pageHref,
                    'title'	    => $i,
                    'next'		=> ($page == $i - 1),
                    'prev'		=> ($page == $i + 1),
                    'selected'	=> ($page == $i)
                );
                if ($page['selected']) {
                    if (!empty($pages)) {
                        $this->setPrevPage ($pages[count($pages)-1]);
                    }
                }
                if (!empty($pages)) {
                    if ($pages[count($pages)-1]['selected']) {
                        $this->setNextPage($page);
                    }
                }
                $pages[] = $page;
                if ($page['prev']) {
                    $this->setPrev($page);
                } elseif ($page['next']) {
                    $this->setNext($page);
                }
                $spaced = false;
                continue ;
            }
            if (!$spaced) {
                $pages[] = array(
                    'href'		=> '',
                    'title'		=> '...',
                    'prev'		=> false,
                    'next'		=> false,
                    'selected'	=> false
                );
                $spaced = true;
            }
        }
        $this->setPages($pages);
        return $this;
    }

    /**
     * Инициализировать пагинатор из _GET
     *
     * @param integer $fullCount
     * @param string $prefix
     * @return Paginator
     */
    public function fromGet($fullCount = 0, $prefix = '')
    {
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
        return new self(
            max($request->get('page'), 1),
            max($request->get('limit', 30), 10),
            $fullCount,
            false
        );
    }

    /**
     * Создание пагинатора через транспорт
     *
     * @param Data_Transport $input Входные данные.
     * @param integer $total Общее количество элементов.
     * @param bool $notGet
     * @return Paginator
     */
    public function fromInput($input, $total = 0, $notGet = false) {
        $perPage = $input->receive('limit');
        if ($input->receive('perPage')) {
            $perPage = $input->receive('perPage');
        }
        $resultRerPage = $perPage ? $perPage : 10;
        return new self(
            max($input->receive('page'), 1),
            $resultRerPage,
            $total,
            $notGet
        );
    }

    /**
     * Возвращает индекс первой записи на текущей страницы
     * (индекс первой записи - 0).
     *
     * @return integer Индекс первой записи или 0.
     */
    public function offset()
    {
        $page = $this->getPage();
        $offset = max(($page - 1) * $this->perPage, 0);
        return $offset;
    }

    /**
     * Получить количество страниц
     *
     * @return integer
     */
    public function pagesCount()
    {
        return $this->getPageCount();
    }

    /**
     * Обновить количество страниц пагинатора
     *
     * @return $this
     */
    public function refreshPageCount()
    {
        $pageCount = $this->_calcPageCount();
        $this->setPageCount($pageCount);
        return $this;
    }

    /**
     * Установить базовый урл пагинатора (без параметра $page)
     *
     * @param string $href
     *
     * @return $this
     */
    public function setHref($href)
    {
        $this->href = $href;
        return $this;
    }

    /**
     * Установить значение свойства next
     *
     * Автор понимает, что нельзя такой метод давать в публичный доступ. Однако, в коде каких-то из проектов
     * может использоваться $paginator->next=..., что вызовет через магию этот метод.
     * Впилено публичным, чтобы не сломать говнокод на тех проектах, где это используется
     * присвоение $paginator->next=....
     *
     * @param mixed $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;
        return $this;
    }

    /**
     * Установить новое значение свойства nextPage
     *
     * @param $nextPage
     *
     * @return $this
     */
    public function setNextPage($nextPage)
    {
        $this->nextPage = $nextPage;
        return $this;
    }

    /**
     * Установить значение свойства notGet
     *
     * @param bool $notGet Новое значение
     *
     * @return $this
     */
    public function setNotGet($notGet)
    {
        $this->notGet = (bool) $notGet;
        return $this;
    }

    /**
     * Установить число элементов на страницу
     *
     * @param int $value
     *
     * @return $this
     */
    public function setPage($value)
    {
        $this->page = $value;
        return $this;
    }

    /**
     * Установить число элементов на страницу
     *
     * @param int $value
     *
     * @return $this
     */
    public function setPerPage($value)
    {
        $this->perPage = $value;
        $this->refreshPageCount();
        $page = $this->getPage();
        $pageCount = $this->getPageCount();
        $nextPage =
            $page < $pageCount
            ? $page + 1
            : $pageCount
        ;
        $this->setNextPage($nextPage);
        return $this;
    }

    /**
     * Установить значение свойства prev
     *
     * @param $prev
     *
     * @return $this
     */
    public function setPrev($prev)
    {
        $this->prev = $prev;
        return $this;
    }

    /**
     * Установить новое значение свойства prevPage
     *
     * @param $prevPage
     *
     * @return $this
     */
    public function setPrevPage($prevPage)
    {
        $this->prevPage = $prevPage;
        return $this;
    }


    /**
     * Установить количество записей пагинатора
     *
     * @param int $total
     *
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = (int) $total;
        $this
            ->buildPages()
            ->refreshPageCount()
        ;
        return $this;
    }
}