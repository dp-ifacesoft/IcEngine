<?php

/**
 * Рендер ошибок
 * 
 * @author morph
 * @Service("errorRender")
 */
class Error_Render extends Manager_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'path'      => 'Error/',
        'layout'    => 'index.tpl'
    );
    
	/**
	 * Рендер
     * 
	 * @var View_Render_Abstract
	 */
	private $render;

	/*
     * Получить текущий рендер
     * 
	 * @return View_Render_Abstract
	 */
	public function getRender()
	{
		return $this->render;
	}
    
    /**
	 * Получить шаблон ошибок
     * 
	 * @param string $code
	 * @return string
	 */
	public function getTemplate($code)
	{
		return $this->config()->path . $code;
	}

    /**
     * Рендеринг ошибки
     *
     * @param Exception $e
     * @throws Exception
     */
	public function render (Exception $e)
	{
        $msg = '[' . $e->getFile() . '@' .
            $e->getLine() . ':' .
            $e->getCode() . '] ' .
            $e->getMessage () . "\n" .
            $e->getTraceAsString() . PHP_EOL;

        $previous = $e->getPrevious();
        if ($previous) {
           $msg .= $previous->getMessage() . "\n" . $previous->getTraceAsString();
        }

        $this->getService('debug')->log($msg, E_ERROR);
        $isVerbose = $this->getService('helperSiteLocation')->get(
            'displayErrors'
        ) || isset($_GET['isVerbose']);
        if (!$isVerbose) {
            throw new Exception($msg);
        }
		if (!$this->render) {
			echo '<pre>' . $msg . $e->getTraceAsString() . '</pre>';
            die;
		} else {
            $this->render->assign('e', $e);
            $template = $this->getTemplate($e->getCode());
            $content = $this->render->fetch($template);
            $this->render->assign('content', $content);
            $layout = $this->config()->path . $this->config->layout;
            $this->render->display($layout);
        }
	}

	/**
	 * Изменить текущий рендер
     * 
	 * @param View_Render_Abstract $render
	 */
	public function setRender(View_Render_Abstract $render)
	{
		if ($render instanceof View_Render_Abstract) {
			$this->render = $render;
		}
	}
}