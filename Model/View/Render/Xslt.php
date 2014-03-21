<?php
/**
 * 
 * @desc Рендер с использованием шаблонизатора Smarty.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class View_Render_Xslt extends View_Render_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		
	);
	
	/**
	 * @desc Объект шаблонизатора
	 * @var Smarty
	 */
	protected $_processor;
	
	/**
	 * @desc Инициализация процессора
	 */
	protected function _afterConstruct ()
	{
		$config = $this->config ();
		$this->_templatesPathes = array_reverse (
			$config ['templates_path']->__toArray ()
		);
		$this->_processor = new XSLTProcessor ();
	}
	
	/**
	 * @desc
	 * @param DOMDocument $xml
	 * @param DOMElement $parent
	 * @param mixed $data
	 */
	protected function _arrayToXml (DOMDocument $xml, DOMElement $parent, $data)
	{
		foreach ($data as $key => $val)
		{
			if (is_numeric ($key))
			{
				$key = 'key' . $key;
			}
			
			if (is_object ($val))
			{
				if (method_exists ($val, '__toArray'))
				{
					$val = $val->__toArray ();
				}
				elseif (method_exists ($val, '__toString'))
				{
					$val = $val->__toString ();
				}
				else
				{
					$val = null;
				}
			}
			
			if (is_array ($val))
			{
				$element = $xml->createElement ($key);
				$parent->appendChild ($element);
				$this->_arrayToXml ($xml, $element, $val);
			}
			else
			{
				$element = $xml->createElement ($key, $val);
				$parent->appendChild ($element);
			}
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl)
	{
		echo $this->fetch ($tpl);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch ($tpl)
	{
		ob_start ();
		$xsl = new DOMDocument ();
		
		$file = $this->findTemplate ($tpl);
		
		if (!$file)
		{
			trigger_error ("xslt template not found: $tpl", E_USER_WARNING);
		}
		
		$xsl->load ($file);
		$this->_processor->importStylesheet ($xsl);
		$this->_processor->transformToURI ($this->xml (), 'php://output');
		return ob_get_clean ();
	}
	
	/**
	 * @desc Возвращает путь до шаблона.
	 * @param type $tpl
	 * @return string 
	 */
	public function findTemplate ($tpl)
	{
		$tpl = $tpl . '.xsl';
		foreach ($this->_templatesPathes as $path)
		{
			$fn = $path . $tpl;
			if (file_exists ($fn))
			{
				return $fn;
			}
		}
		
		return null;
	}
	
	/**
	 * @desc Возвращает используемый экземпляр шаблонизатора.
	 * @return XSLTProcessor
	 */
	public function processor ()
	{
		return $this->_processor;
	}
	
	/**
	 * @desc Формирует XML документ, содержащий данные для вывода
	 * @return DOMDocument
	 */
	public function xml ()
	{
		$xml = new DOMDocument ('1.0', 'UTF-8');
        $root = $xml->createElement ('Input');
		$xml->appendChild ($root);
		$this->_arrayToXml ($xml, $root, $this->_vars);
		$xml->save ('D:/temp/1.xml');
        return $xml;
	}
	
}