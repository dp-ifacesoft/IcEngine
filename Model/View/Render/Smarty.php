<?php

/**
 *
 * @desc Рендер с использованием шаблонизатора Smarty.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class View_Render_Smarty extends View_Render_Abstract
{

    /**
     * @desc Объект шаблонизатора
     * @var Smarty
     */
    protected $_smarty;

    /**
     * @desc Конфиг
     * @var array
     */
    protected static $_config = array(
        /**
         * @desc Директория для скопилированных шаблонов Smarty
         * @var string
         */
        'compile_path' => 'cache/templates',
        /**
         * @desc Путь для лоадера до смарти
         * @var string
         */
        'smarty_path' => 'smarty3/Smarty.class.php',
        /**
         * @desc Пути до шаблонов
         * @var array
         */
        'templates_path' => array(),
        /**
         * @desc Пути до плагинов
         * @var array
         */
        'plugins_path' => array(),
        /**
         * @desc Фильры
         * @var array
         */
        'filters' => array(
            'Dblbracer'
        )
    );

    protected function _afterConstruct()
    {
        $config = $this->config();
        Loader::requireOnce($config ['smarty_path'], 'includes');

        $this->_smarty = new Smarty ();

        $this->_smarty->compile_dir = $config ['compile_path'];
        $this->_smarty->template_dir = array_reverse(
            $config ['templates_path']->__toArray()
        );
        $this->_smarty->plugins_dir = $config ['plugins_path']->__toArray();

        // Фильтры
        foreach ($config ['filters'] as $filter) {
            $filter = 'Helper_Smarty_Filter_' . $filter;
            $filter::register($this->_smarty);
        }
    }

    /**
     * @desc Получает идентификатор компилятор для шаблона.
     * Необходимо, т.к. шаблон зависит от путей шаблонизатора.
     * @param string $tpl
     * @return string
     */
    protected function _compileId($tpl)
    {
        return crc32(json_encode($this->_smarty->template_dir));
    }

    /**
     * @desc Добавление пути до директории с плагинами Smarty
     * @param string|array $path Директории с плагинами
     */
    public function addPluginsPath($path)
    {
        $this->_smarty->plugins_dir = array_merge(
            (array)$this->_smarty->plugins_dir,
            (array)$path
        );
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::addTemplatesPath()
     */
    public function addTemplatesPath($path)
    {
        $this->_smarty->template_dir = array_merge(
            array_reverse((array)$path),
            (array)$this->_smarty->template_dir
        );
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::addHelper()
     */
    public function addHelper($helper, $method)
    {
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::assign()
     */
    public function assign($key, $value = null)
    {
        if (is_array($key)) {
            $this->_smarty->assign($key);
        } else {
            $this->_smarty->assign($key, $value);
        }
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::display()
     */
    public function display($tpl)
    {
        $tpl .= '.tpl';
        return $this->_smarty->display($tpl, null, $this->_compileId($tpl));
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::fetch()
     */
    public function fetch($tpl)
    {
        $tpl .= '.tpl';

        return $this->_smarty->fetch($tpl);
    }

    /**
     * @desc Возвращает массив путей до шаблонов.
     * @return array
     */
    public function getTemplatesPathes()
    {
        return $this->_smarty->template_dir;
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::getVar()
     */
    public function getVar($key)
    {
        return $this->_smarty->getTemplateVars($key);
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::popVars()
     */
    public function popVars()
    {
        $this->_smarty->clearAllAssign();
        $this->_smarty->assign(array_pop($this->_varsStack));
    }

    /**
     * (non-PHPdoc)
     * @see View_Render_Abstract::pushVars()
     */
    public function pushVars()
    {
        $this->_varsStack [] = $this->_smarty->getTemplateVars();
        $this->_smarty->clearAllAssign();
    }

    /**
     * @desc Возвращает используемый экземпляр шаблонизатора.
     * @return Smarty
     */
    public function smarty()
    {
        return $this->_smarty;
    }

}