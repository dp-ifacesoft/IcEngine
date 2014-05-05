<?php

/**
 * Фабрика модулей
 *
 * @author neon
 */
class Module extends Model_Defined
{
	/**
	 * @inheritdoc
	 */
	public static $rows = array(
		array(
			'id'		=> 1,
			'name'		=> 'Ice',
			'isMain'	=> true,
			'hasResource'	=> array(
				'css', 'js'
			)
		),
		array(
			'id'		=> 2,
			'name'		=> 'Admin',
			'isMain'	=> false,
			'hasResource'	=> array(
				'css', 'js'
			)
		)
	);

	public static function notMain()
	{
		$out = array();
		foreach (self::$rows as $row) {
			if (!$row['isMain']) {
				$out[] = $row;
			}
		}
		return $out;
	}

	/**
	 * Получить путь модуля

	 * @return string
	 */
	public function path()
	{
		return $this->name . '/';
	}
}