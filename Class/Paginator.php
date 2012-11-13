<?php
/**
 *
 * @desc Объект для хранения списка страниц.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Paginator
{

	/**
	 * Флаг означает, что ссылки нам нужны без всяких ?&page
	 * @var bool
	 */
	public $notGet = false;

	/**
	 * @desc Общее количество элементов
	 * @var integer
	 */
	public $fullCount;

	/**
	 * @desc Ссылка на страницу.
	 * Если на задана, будешь использован адрес из запроса.
	 * @var string
	 */
	public $href;

	/**
	 * @desc Текущая страница
	 * @var integer
	 */
	public $page;

	/**
	 * @desc Количество элементов на странице
	 * @var integer
	 */
	public $pageLimit = 30;

	/**
	 * @desc Сформированные для вывода номера страниц
	 * array (
	 * 		'href'	=> ссылка на страница
	 * 		'title'	=> номер страницы или многоточие
	 * )
	 * @var array
	 */
	public $pages;

	/**
	 * @desc Предыдущая страница
	 * @var array
	 */
	public $prev;

	/**
	 * @desc Следующая страница
	 * @var array
	 */
	public $next;

	/**
	 *
	 * @param integer $page Текущая страница
	 * @param integer $page_limit Количество элементов на странице
	 * @param integer $full_count Полное количество элементов
	 */
	public function __construct ($page, $page_limit = 30, $full_count = 0, $notGet = false)
	{
		$this->page = $page;
		$this->pageLimit = $page_limit;
		$this->fullCount = $full_count;
		$this->notGet = $notGet;
	}

	/**
	 * @desc Заполнение массива страниц со ссылками.
	 */
	public function buildPages ()
	{
		$this->pages = array ();
		$pages_count = $this->pagesCount ();

		if ($pages_count <= 1)
		{
			return ;
		}

		$half_page = round ($pages_count / 2);
		$spaced = false;

		$href = isset ($this->href) ? $this->href : Request::uri (false);

		// Удаление из запроса GET параметра page
		$p = 'page';
		$href = preg_replace (
			"/((?:\?|&)$p(?:\=[^&]*)?$)+|((?<=[?&])$p(?:\=[^&]*)?&)+|((?<=[?&])$p(?:\=[^&]*)?(?=&|$))+|(\?$p(?:\=[^&]*)?(?=(&$p(?:\=[^&]*)?)+))+/",
			'',
			$href
		);
		if (!$this->notGet) {
			if (strpos ($href, '?') === false) {
				$href .= '?page=';
			} else {
				$href .= '&page=';
			}
		} else {
			if ($this->page > 1) {
				$href = substr($href, 0, (int) (strlen((string) $this->page) + 1) * -1);
			}
		}

		for ($i = 1; $i <= $pages_count; $i++)
		{
			if (
				$i <= 3 ||							// первые 3 страницы
				($pages_count - $i) < 3 ||			// последние 3 страницы
				abs ($half_page - $i) < 3 ||		// середина
				abs ($this->page - $i) < 3			// возле текущей
			)
			{
				$pageHref = $href . ($i > 1 ?
					$i . ($this->notGet ? '/' : '') : '');
				// Ссылка с номером страницы
				$page = array (
					'href'	    => $pageHref,
					'title'	    => $i,
					'next'		=> ($this->page == $i - 1),
					'prev'		=> ($this->page == $i + 1),
					'selected'	=> ($this->page == $i)
				);
				$this->pages [] = $page;

				if ($page ['prev'])
				{
					$this->prev = $page;
				}
				elseif ($page ['next'])
				{
					$this->next = $page;
				}

				$spaced = false;
				continue ;
			}

			if (!$spaced)
			{
				$this->pages [] = array (
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
	 * @param integer $full_count
	 * @param string $prefix
	 * @return Paginator
	 */
	public static function fromGet ($full_count = 0, $prefix = '')
	{
		return new self (
			max (Request::get ('page'), 1),
			max (Request::get ('limit', 30), 10),
			$full_count,
			false
		);
	}

	/**
	 *
	 * @param Data_Transport $input Входные данные.
	 * @param integer $full_count Общее количество элементов.
	 * @return Paginator
	 */
	public static function fromInput (Data_Transport $input, $full_count = 0, $notGet = false)
	{
		return new self (
			max ($input->receive ('page'), 1),
			max ($input->receive ('limit'), 10),
			$full_count,
			$notGet
		);
	}
	/**
	 * @return integer
	 * @desc возвращает текущий начальный индекс
	 * @deprecated следует использовать метод Paginator::offset ().
	 */
	public function getIndex ()
	{
		return max ($this->page - 1, 0) * $this->pageLimit;
	}
	/**
	 * @desc Возвращает индекс первой записи на текущей страницы
	 * (индекс первой записи - 0).
	 * @return integer Индекс первой записи или 0.
	 */
	public function offset ()
	{
		$offset = max (($this->page - 1) * $this->pageLimit, 0);
		return $offset;
	}

	/**
	 * @return integer
	 */
	public function pagesCount ()
	{
		if ($this->pageLimit > 0)
		{
			return ceil ($this->fullCount / $this->pageLimit);
		}
		else
		{
			return 1;
		}
	}

}