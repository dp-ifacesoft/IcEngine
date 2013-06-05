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
	public $notGet = false;

	/**
	 * Общее количество элементов
	 *
     * @var integer
	 */
	public $total;

	/**
	 * Ссылка на страницу.
	 * Если на задана, будешь использован адрес из запроса.
	 *
     * @var string
	 */
	public $href;

	/**
	 * Текущая страница
	 *
     * @var integer
	 */
	public $page;

    /**
     * Количество страниц
     *
     * @var integer
     */
    public $pageCount;

	/**
	 * Количество элементов на странице
     *
	 * @var integer
	 */
	public $perPage = 30;

	/**
	 * Сформированные для вывода номера страниц
	 * array(
	 *      'href'	=> ссылка на страница
	 * 		'title'	=> номер страницы или многоточие
	 * )
	 *
     * @var array
	 */
	public $pages;

	/**
	 * Предыдущая страница
	 *
     * @var array
	 */
	public $prev;

    /**
     * Предыдущая страница от выбранной
     *
     * @var array
     */
    public $prevPage;

    /**
	 * Следующая страница
	 *
     * @var array
	 */
	public $next;

    /**
     * Следующая страница от выбранной
     *
     * @var array
     */
    public $nextPage;

	/**
	 * Конструктор
     *
	 * @param integer $page Текущая страница
	 * @param integer $page_limit Количество элементов на странице
	 * @param integer $full_count Полное количество элементов
	 * @param boolean $notGet ЧПУ стиль
	 */
	public function __construct($page, $perPage = 30,
        $total = 0, $notGet = false)
	{
		$this->page = $page;
		$this->perPage = $perPage;
        $this->prevPage = $page > 1 ? ($page - 1) : 1;
        $pageCount = ceil($total / $perPage);
        $this->nextPage = $page < $pageCount ? ($page + 1) : $pageCount;
		$this->total = $total;
		$this->notGet = $notGet;
	}

	/**
	 * Заполнение массива страниц со ссылками.
	 */
	public function buildPages()
	{
		$locator = IcEngine::serviceLocator();
		$request = $locator->getService('request');
		$this->pages = array();
		$pagesCount = $this->pagesCount();
		if ($pagesCount <= 1) {
			return;
		}
		$halfPage = round($pagesCount / 2);
		$spaced = false;
		// Удаление из запроса GET параметра page
		$p = 'page';
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
		} elseif ($this->page > 1) {
            $href = substr(
                $href, 0, (int) (strlen((string) $this->page) + 1) * -1
            );
		}
		for ($i = 1; $i <= $pagesCount; $i++) {
			if ($i <= 3 ||							// первые 3 страницы
				($pagesCount - $i) < 3 ||			// последние 3 страницы
				abs($halfPage - $i) < 3 ||			// середина
				abs($this->page - $i) < 3			// возле текущей
			) {
                $pageHref = $href;
                if (!($i == 1 && $this->notGet)) {
                    $pageHref .= $i;
                }
                if ($this->notGet && $i != 1) {
                    $pageHref .= '/';
                }
				// Ссылка с номером страницы
				$page = array(
					'href'	    => $pageHref,
					'title'	    => $i,
					'next'		=> ($this->page == $i - 1),
					'prev'		=> ($this->page == $i + 1),
					'selected'	=> ($this->page == $i)
				);
                if ($page['selected']) {
                    if (!empty($this->pages)) {
                        $this->prevPage = $this->pages[count($this->pages)-1];
                    }
                }
                if (!empty($this->pages)) {
                    if ($this->pages[count($this->pages)-1]['selected']) {
                        $this->nextPage = $page;
                    }
                }
				$this->pages[] = $page;
				if ($page['prev']) {
					$this->prev = $page;
				} elseif ($page['next']) {
					$this->next = $page;
				}
				$spaced = false;
				continue ;
			}
			if (!$spaced) {
				$this->pages[] = array(
					'href'		=> '',
					'title'		=> '...',
					'prev'		=> false,
					'next'		=> false,
					'selected'	=> false
				);
				$spaced = true;
			}
		}
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
	 * @param Data_Transport_Abstract $input Входные данные.
	 * @param integer $total Общее количество элементов.
	 * @return Paginator
	 */
	public function fromInput($input, $total = 0, $notGet = false) {
		$perPage = $input->receive('limit');
        if ($input->receive('perPage')) {
            $perPage = $input->receive('perPage');
        }
        $perPage = $perPage ? $perPage : 10;
        return new self(
			max($input->receive('page'), 1),
			$perPage,
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
		$offset = max(($this->page - 1) * $this->perPage, 0);
		return $offset;
	}

	/**
     * Получить количество страниц
     *
	 * @return integer
	 */
	public function pagesCount()
	{
		if ($this->perPage > 0) {
			if ($this->total) {
				return ceil($this->total / $this->perPage);
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
	 * Установить число элементов на страницу
	 *
	 * @param int $value
	 */
	public function setPerPage($value)
	{
		$this->perPage = $value;
        $this->pageCount = ceil($this->total / $this->perPage);
        $this->nextPage = $this->page < $this->pageCount ? $this->page + 1 :
            $this->pageCount;
	}
}