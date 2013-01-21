<?php

/**
 * Временный контент - специальная модель, предназначенная для
 * хранения дополнительной информации о форме для редактирования.
 *
 * @author goorus, morph
 * @Service("tempContent")
 *
 */
class Temp_Content extends Model
{
	/**
	 * Данные
	 *
	 * @var type
	 */
	protected $data = null;

	/**
	 * Созданные за этот запрос
	 *
     * @param 0 = name
	 * @param 1 = value
	 * @var array
	 */
	protected $created = array ();

	/**
	 * Устанавливаем дату, ключ = значение
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function setAttr($key, $value)
	{
		$this->data[$key] = $value;
		$this->update(array(
			'json' => urlencode(json_encode($this->data))
		));
	}

	/**
	 * Переопределяем аттрибуты
	 *
	 * @return type
	 */
	public function attr($key, $value = null)
	{
		if (is_null($this->data)) {
			$this->data = json_decode(urldecode($this->json), true);
		}
		$args = func_get_args();
		if (count($args) == 1 && !is_array($args[0])) {
			return isset($this->data[$args[0]])
					? $this->data[$args[0]] : null;
		} elseif (is_array($args[0])) {
			foreach ($args[0] as $key => $value) {
				$this->setAttr($key, $value);
			}
		} elseif (count($args) == 2) {
			$this->setAttr($args[0], $args[1]);
		}
	}

	/**
	 * @desc Возвращет временный контент по коду
	 * @param string $utcode
	 * @return Temp_Content
	 */
	public function byUtcode ($utcode)
	{
		$modelManager = $this->getService('modelManager');
        return $modelManager->byOptions(
			__CLASS__,
            array(
                'name'  => '::Key',
                'key'   => (string) $utcode
            )
		);
	}

	/**
	 * @desc Создает новый временный контент
	 * @param string|Controller_Abstract $controller Контроллер или название
	 * @param string $table
	 * @param integer $row_id
	 * @return Temp_Content
	 */
	public function create ($controller, $table = '',
		$row_id = 0, $data = null)
	{
		$userService = $this->getService('user');
        $helperDate = $this->getService('helperDate');
        $requestService = $this->getService('request');

        $utcode = $this->genUtcode();
		$tc = new Temp_Content (array (
			'time'			=> $helperDate->toUnix(),
			'utcode'		=> $utcode,
			'json'			=> json_encode($data),
			'ip'			=> $requestService->ip(),
			'controller'	=>
				$controller instanceof Controller_Abstract ?
				$controller->name() :
				$controller,
			'table'			=> $table,
			'rowId'			=> (int) $row_id,
			'day'			=> $helperDate->eraDayNum(),
			'User__id'		=> $userService->id()
		));

		return $tc->save();
	}

	/**
	 * @desc Возвращает временный контент для модели на этом запросе
	 * @param Model $model
	 * @param Controller_Abstract $controller
	 * @return Temp_Content
	 */
	public function getFor(Model $model,
		Controller_Abstract $controller = null)
	{
		$mname = $model->modelName();
		$mkey = $model->key();

		if (!isset($this->$_created[$mname]))
		{
			$this->$_created[$mname] = array();
		}

		if (!isset ($this->$_created[$mname][$mkey]))
		{
			$this->$_created[$mname][$mkey] = self::create(
				$controller ? $controller->name() : '',
				$model->table(),
				$mkey
			);
		}

		return $this->$_created[$mname][$mkey];
	}

	/**
	 * @desc Генерация уникального кода
	 * @return string
	 */
	public static function genUtcode()
	{
		// ucac7fe407f8e5e1c683005867edd74439452c4.39068717
		$u = uniqid('', true);
		// Вырезаем точку
		return md5(time()) . substr($u, 9, 5) . substr($u, 15);
	}

	public static function idForNew(Temp_Content $tc)
	{
		return $tc->utcode;
	}

	/**
	 *
	 * @param Model $item
	 * @param array $components
	 * @return Temp_Content
	 */
	public function rejoinComponents(Model $item, array $components)
	{
		foreach ($components as $component)
		{
			$this->component($component)->rejoin($item);
		}
		return $this;
	}

}