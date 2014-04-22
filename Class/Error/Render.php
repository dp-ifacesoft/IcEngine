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
        'path' => 'Error/',
        'layout' => 'index.tpl'
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
    public function render(Exception $e)
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : './cli';

        $referer = isset($_SERVER['HTTP_REFERER']) ? '<i>referer: ' . $_SERVER['HTTP_REFERER'] . "</i>\n\n" : "\n";
        $msg = '<i>url: ' . $requestUri . "</i>\n" . $referer .
            '<b style="color: red;">[' . $e->getFile() . '@' .
            $e->getLine() . ':' .
            $e->getCode() . '] ' .
            $e->getMessage() . "</b>\n" .
            $e->getTraceAsString() . "\n\n";

        $previous = $e->getPrevious();
        if ($previous) {
            $msg .= "<b>" . $previous->getMessage() . "</b>\n" . $previous->getTraceAsString();
        }

        $this->getService('debug')->log($msg, E_ERROR);
        $isVerbose = $this->getService('helperSiteLocation')->get(
                'displayErrors'
            ) || isset($_GET['isVerbose']);
        if (!$isVerbose) {
//            throw new Exception($msg);
            Debug::outputErrors($e);
        }
        if ($this->render) {
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