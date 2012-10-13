<?php
/**
 *
 * @desc Шаблоны сообщений.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Template extends Model_Child
{

	/**
	 * @desc Данные для пустого шаблона
	 * @var array
	 */
	public static $blankTemplate = array (
		'id'		=> 0,
		'name'	    => 'empty',
		'parentId'	=> 0,
		'subject'	=> 'subj',
		'body'	    => 'body'
	);

	/**
	 * Возращается шаблон по имени, либо шаблон по умолчанию
	 * @param string $name
	 * @param boolean $blank Вернуть базовый, если шаблон не найден
	 * @return Mail_Template
	 */
	public static function byName ($name, $blank = true)
	{
		$template = Model_Manager::byQuery (
		    'Mail_Template',
		    Query::instance ()
		   		->where ('name', $name)
		);

		if (!$template && $blank)
		{
			$template = new Mail_Template (self::$blankTemplate);
		}

		return $template;
	}

	/**
	 * @desc Получение тела по шаблону.
	 * @param array $data Переменные шаблона.
	 * @return string
	 */
	public function body (array $data = array ())
	{
		$smarty = View_Render_Manager::pushViewByName ('Smarty')->smarty ();
		$smarty->assign ($data);

		$tpl_name = 'Mail/Template/' . $this->name . '.tpl';
		if($smarty->templateExists($tpl_name))
		{
			$body = $smarty->fetch ($tpl_name);
		}
		else
		{
			$body = $smarty->fetch ('string:' . $this->body);
		}

		View_Render_Manager::popView ();

		$parent = $this->getParent ();

		if ($parent)
		{
		    $data ['body'] = $body;
		    $body = $parent->body ($data);
		}

		return $body;
	}

	/**
	 * @desc Получение заголовка по шаблону.
	 * @param array $data
	 * @return string
	 */
	public function subject (array $data = array ())
	{
		if (!$this->subject)
		{
			// пустая тема
			return '';
		}

		$smarty = View_Render_Manager::pushViewByName ('Smarty')->smarty ();

		$smarty->assign ($data);

		$result = $smarty->fetch ('string:' . $this->subject);
		View_Render_Manager::popView ();
		return $result;
	}

}