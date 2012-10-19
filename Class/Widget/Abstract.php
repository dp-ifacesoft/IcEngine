<?php
/**
 *
 * @desc Абстрактный класс виджета.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 * @deprecated Следует использовать Controller_Manager
 *
 */
abstract class Widget_Abstract
{

	/**
	 *
	 * @var Data_Transport
	 */
	protected $_input;

	/**
	 *
	 * @var Data_Transport
	 */
	protected $_output;

	/**
	 * Шаблон
	 * @var string
	 */
	protected $_template;

	public final function __construct ()
	{
		$this->_input = new Data_Transport ();
		$this->_output = new Data_Transport ();
	}

	/**
	 *
	 * @param string $text
	 * 		Системный текст ошибки (не должен отображаться пользователю)
	 * @param string $method
	 * 		Метод, в котором произошла ошибка (используется при формировании
	 * 		имени шаблона. Если не указан, шаблон использован не будет.
	 * @param string $sub
	 * 		Дополнительный шаблон ошибки.
	 */
	public function _error ($text, $method = null, $sub = null)
	{
		$this->_output->send (array (
			'error'	=> $text,
			'data'	=> array (
				'error'	=> $text
			)
		));
		if ($method)
		{
			$this->_template = Helper_Action::path ($method, $sub);
		}
		else
		{
			$method = Widget_Manager::NULL_TEMPLATE;
		}
	}

	/**
	 *
	 * @param string $method
	 * @return string
	 */
	public function template ($method)
	{
		if (!$this->_template)
		{
			$template = str_replace (
				array ('_', '::'),
				'/',
				get_class ($this)
			) . '/' . $method;
		}
		else
		{
			$template = $this->_template;
			$this->_template = null;
		}

		return $template;
	}

	/**
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_input;
	}

	/**
	 * @return Data_Transport
	 */
	public function getOutput ()
	{
		return $this->_output;
	}

	/**
	 *
	 * @param Data_Transport $input
	 * @return Widget_Abstract
	 */
	public function setInput (Data_Transport $input)
	{
		$this->_input = $input;
		return $this;
	}

	/**
	 *
	 * @param Data_Transport $output
	 * @return Widget_Abstract
	 */
	public function setOutput (Data_Transport $output)
	{
		$this->_output = $output;
		return $this;
	}

	public function setTemplate ($template)
	{
		$this->_template = $template;
	}

	/**
	 * Получение шаблона вида
	 * "Название/Виджета/метод/$tpl"
	 * @param string $method __METHOD__
	 * @param string $tpl Шаблон
	 * @return string
	 */
	public function tplFor ($method, $tpl)
	{
		return
			str_replace (array ('_', '::'), '/', $method) . '/' . $tpl;
	}

	/**
	 * Название виджета (без приставки "Widget_")
	 * @return string
	 */
	public function widgetName ()
	{
		return substr (get_class ($this), 7);
	}
}