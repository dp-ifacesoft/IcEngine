<?php

/**
 * Шаблоны сообщений.
 * 
 * @author goorus, morph
 * @Service("mailTemplate")
 * @Orm\Entity
 */
class Mail_Template extends Model
{
    /**
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)
     * @Orm\Index\Primary
     */
    public $id;
    
    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     * @Orm\Index\Key
     */
    public $name;
    
    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     * @Orm\Index\Key
     */
    public $parentId;
    
    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $subject;
    
    /**
     * @Orm\Field\Text(Not_Null)
     */
    public $body;
    
	/**
	 * Данные для пустого шаблона
     * 
	 * @var array
	 */
	public static $blankTemplate = array(
		'id'		=> 0,
		'name'	    => 'empty',
		'parentId'	=> 0,
		'subject'	=> 'subj',
		'body'	    => 'body'
	);

    /**
	 * Получение тела по шаблону.
	 * 
     * @param array $data Переменные шаблона.
	 * @return string
	 */
	public function body(array $data = array())
	{
		$viewRenderManager = $this->getService('viewRenderManager');
		$smarty = $viewRenderManager->pushViewByName('Smarty')->smarty();
		$smarty->assign($data);
		$template = 'Mail/Template/' . $this->name . '.tpl';
		if($smarty->templateExists($template)) {
			$body = $smarty->fetch($template);
		} else {
			$body = $smarty->fetch('string:' . $this->body);
		}
		$viewRenderManager->popView ();
        $parent = null;
        if ($this->parentId) {
            $parent = $this->getService('modelManager')->byOptions(
                'Mail_Template', array(
                    'name'  => '::Key',
                    'key'    => $this->parentId
                ));
        }
		if ($parent) {
		    $data['body'] = $body;
		    $body = $parent->body($data);
		}
		return $body;
	}
    
	/**
	 * Возращается шаблон по имени, либо шаблон по умолчанию
     * 
	 * @param string $name
	 * @param boolean $blank Вернуть базовый, если шаблон не найден
	 * @return Mail_Template
	 */
	public function byName($name, $blank = true)
	{
		$modelManager = $this->getService('modelManager');
		$template = $modelManager->byOptions(
            'Mail_Template',
            array(
                'name'  => '::Name',
                'value' => $name
            )
        );
		if (!$template && $blank) {
			$template = new Mail_Template(self::$blankTemplate);
		}
		return $template;
	}

	/**
	 * Получение заголовка по шаблону.
	 * 
     * @param array $data
	 * @return string
	 */
	public function subject(array $data = array())
	{
		if (!$this->subject) {
			// пустая тема
			return '';
		}
		$viewRenderManager = $this->getService('viewRenderManager');
		$smarty = $viewRenderManager->pushViewByName('Smarty')->smarty();
		$smarty->assign($data);
		$result = $smarty->fetch('string:' . $this->subject);
		$viewRenderManager->popView();
		return $result;
	}
}