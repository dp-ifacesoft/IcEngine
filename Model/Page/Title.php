<?php

/**
 * Заголовки сайта
 * 
 * Created at: 2012-10-04 09:57:43
 * @author morph
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
     * Получить тайтл по городу и урлу
     *
     * @param string $cityId
     * @param string $uri
     * @return null
     */
	public function byAddress($cityId, $uri)
	{
        $modelManager = $this->getService('modelManager');
		$page = $modelManager->byOptions(
			'Page_Title',
			'::Active',
			array(
				'name'	=> '::City',
				'id'	=> array(0, $cityId)
			),
			array(
				'name'	=> 'Pattern',
				'value'	=> $uri
			),
			array(
				'name'	=> '::Order_Desc',
				'field'	=> 'City__id'
			),
			array(
				'name'	=> '::Order_Desc',
				'field'	=> 'weight'
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
					static $keys = array('siteTitle', 'pageTitle',
						'controllerAction');
					foreach ($keys as $key) {
						if (!empty($data[$key])) {
							$page->set($key, $data[$key]);
						}
					}
				}
			}
		}
		return $page ? $page->compile() : null;
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
				'name'	=>	'Pattern',
				'value'	=>	$pattern
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
		if ($this->controllerAction &&
			strpos($this->controllerAction, '/') !== false) {
            $dataTransportManager = $this->getService('dataTransportManager');
			$transport = $dataTransportManager->get('default_input');
			list($controller, $action) = explode('/', $this->controllerAction);
            $controllerManager = $this->getService('controllerManager');
			$task = $controllerManager->call(
				$controller, $action,
				$transport->receiveAll()
			);
			if ($task) {
				$transaction = $task->getTransaction();
				if ($transaction) {
					$buffer = $transaction->buffer();
					if ($buffer) {
						$fields = $this->getFields();
						foreach ($fields as $field => $data) {
							if (strpos($data, '{') === false) {
								continue;
							}
							foreach ($buffer as $key => $value) {
								if (!is_scalar($value)) {
									continue;
								}
								$data = str_replace(
									'{' . $key . '}', $value, $data);
							}
							$this->set($field, $data);
						}
					}
				}
			}
		}
		return $this;
	}
    
    /**
     * Компилирует тайтлы
     * 
     * @param string $titles
     * @param array $data
     */
    public function compileTitles($titles, $data)
    {
        $helperString = $this->getService('helperString');
        $row = array(
            'pageTitle' => $titles[0],
            'siteTitle' => $titles[1]
        );
        $fields = array('pageTitle', 'siteTitle');
        return array_values(
            $helperString->normalizeFields($row, $fields, $data)
        );
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
				'pageTitle'			=> '',
				'siteTitle'			=> '',
				'controllerAction'	=> '',
				'pageText'			=> '',
				'keywords'			=> '',
				'description'		=> ''
			)
		);
	}
}