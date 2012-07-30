<?phpif (!class_exists ('Config_Array')){	include dirname (__FILE__) . '/Abstract.php';}class Config_Xml extends Config_Array{	/**	 * 	 * @param string $xml	 * 		Путь до файла или строка, содержащая XML документ	 * @param string $section	 * 		Загружаемая секция. Если не указана, документ будет загружен полностью.	 */	public function __construct ($xml, $section = '')	{		$data = array ();        $config = null;		if (strstr ($xml, '<?xml')) 		{			$config = simplexml_load_string ($xml);		} 		else 		{			$config = simplexml_load_file ($xml);		}		if (!$config)		{		    Loader::load ('Zend_Exception');			throw new Zend_Exception ('Bad xml config data.');		}				if (!$section)		{			foreach ($config as $sectionName => $sectionData) 			{				$data [$sectionName] = $this->processExtends ($config, $sectionName);			}		}		else if (is_array ($section)) 		{			foreach ($section as $sectionName) 			{				if (isset ($config->$sectionName)) 				{					$data = array_merge ($this->processExtends ($config, $sectionName), $data);				}			}		}		else 		{			if (isset ($config->$section)) 			{				$data = $this->processExtends ($config, $section);				if (!is_array ($data)) 				{					$data = array ($section=>$data);				}			}		}		parent::__construct ($data);	}	protected function processExtends (SimpleXMLElement $element, $section)	{		if (isset ($element->$section)) 		{			$thisSection = $element->$section;			if (isset ($thisSection ['extends'])) 			{				$extendedSection = (string) $thisSection ['extends'];				$config = $this->processExtends ($element, $extendedSection, $config);				$config = $this->arrayMergeRecursive ($config, $this->toArray ($thisSection));				return $config;			}						return array();					}	}	protected function toArray (SimpleXMLElement $xmlObject)	{		$config = array();		if (count ($xmlObject->attributes ()) > 0) 		{			foreach ($xmlObject->attributes () as $key => $value) 			{				if ($key === 'extends')				{					continue;				}				$value = (string) $value;				if (array_key_exists ($key, $config)) 				{					if (!is_array($config[$key])) 					{						$config[$key] = array ($config[$key]);					}					$config[$key][] = $value;				} 				else 				{					$config[$key] = $value;				}			}		}		if (count ($xmlObject->children ()) > 0) 		{			foreach ($xmlObject->children () as $key => $value) 			{				if (count ($value->children ()) > 0) 				{					$value = $this->toArray ($value);				} 				else if (count($value->attributes ()) > 0)				{					$attributes = $value->attributes ();					if (isset ($attributes['value'])) 					{						$value = (string) $attributes['value'];					} 					else					{						$value = $this->toArray ($value);					}				} 				else 				{					$value = (string) $value;				}				if (array_key_exists ($key, $config)) 				{					if (!is_array ($config[$key]) || !array_key_exists (0, $config[$key])) 					{						$config[$key] = array ($config[$key]);					}					$config[$key][] = $value;				} 				else 				{					$config[$key] = $value;				}			}		} 		else if (!isset ($xmlObject['extends']) && (count ($config) === 0)) 		{			$config = (string) $xmlObject;		}		return $config;	}	protected function arrayMergeRecursive($firstArray, $secondArray)	{		if (is_array ($firstArray) && is_array ($secondArray)) 		{			foreach ($secondArray as $key=>$value) 			{				if (isset ($firstArray[$key])) 				{					$firstArray[$key] = $this->arrayMergeRecursive ($firstArray[$key], $value);				} 				else 				{					if($key === 0) 					{						$firstArray = array ($this->arrayMergeRecursive ($firstArray, $value));					} 					else 					{						$firstArray[$key] = $value;					}				}			}		} 		else 		{			$firstArray = $secondArray;		}		return $firstArray;	}}