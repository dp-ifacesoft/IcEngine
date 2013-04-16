<?php

/**
 * Менеджер ресурсов представления
 * 
 * @author goorus, morph
 * @Service("viewResourceManager")
 */
class View_Resource_Manager extends Manager_Abstract
{
	/**
	 * Тип ресурса - CSS. Файл стилей.
	 */
	const CSS = 'css';

	/**
	 * Тип ресурса - JS. Файл javascript.
	 */
	const JS = 'js';

	/**
	 * Тип ресурса - JTPL. Шаблоны для javascript.
	 */
	const JTPL = 'jtpl';

	/**
	 * Модели, упакованные в js
	 */
	const JRES = 'jres';

	/**
	 * @inheritdoc
	 */
	protected $config = array();

	/**
	 * Ресурсы
     * 
	 * @var array <View_Resource_Item>
	 */
	protected $resources = array();

	/**
	 * Упаковщики ресурсов.
	 * 
     * @var array <View_Resrouce_Packer_Abstract>
	 */
	protected $packers = array();

	/**
	 * Добавление ресурса
	 * 
     * @param string|array $data Ссылка на ресурс или массив пар (тип => ссылка)
	 * @param string $type [optional] Тип ресурса
	 * @param array $flags Параметры
	 * @return View_Resource|array
	 */
	public function add($data, $type = null, array $options = array())
	{
		if (is_array($data)) {
			$result = array();
			foreach ($data as $d) {
				$result[$d] = $this->add($d, $type, $options);
			}
			return $result;
		}
		if (is_null($type)) {
			$type = strtolower(substr(strrchr($data, '.'), 1));
		}
		if (!isset($this->resources[$type])) {
			$this->resources[$type] = array();
		} else {
			foreach ($this->resources[$type] as &$exists) {
				if ($exists->href == $data) {
					return $exists;
				}
			}
		}
		$options['href'] = $data;
		$result = new View_Resource($options);
		$this->resources[$type][] = $result;
		return $result;
	}

	/**
	 * Возвращает ресурсы указанного типа.
	 * 
     * @param string $type Тип
	 * @return array Ресурсы
	 */
	public function getData($type)
	{
		if (!isset($this->resources[$type])) {
			return array();
		}
		return $this->resources[$type];
	}

	/**
	 * Загружает ресурсы
	 * 
     * @param string $baseUrl
	 * @param string $baseDir
	 * @param array|objective <string> $dirs
	 * @param string $type
	 */
	public function load($baseUrl, $baseDir, $dirs, $type = null)
	{
		$baseDirFiltered = str_replace('\\', '/', $baseDir);
		$baseDir = rtrim($baseDirFiltered, '/') . '/';
        $baseUrl = $baseUrl ?: $baseDir;
        $loaded = array();
		foreach ($dirs as $pattern) {
            $results = $this->loadWithPattern($pattern, $baseDir, $baseUrl);
            if ($results) {
                foreach ($results as $result) {
                    $loaded[] = $this->add($result['file'], $type, $result['options']);
                }
            }
        }
        return $loaded;
	}
    
    /**
     * Включает все файлы директории
     * 
     * @param string $pattern
     * @param integer $singleStarPos
     * @param string $baseDir
     * @param string $baseUrl
     * @return array
     */
    protected function includeFiles($pattern, $singleStarPos, $baseDir, 
        $baseUrl)
    {
        $dir = trim(substr($pattern, 0, $singleStarPos), '/');
        $pattern = substr($pattern, $singleStarPos);
        $iterator = new DirectoryIterator($baseDir . '/' . $dir);
        $result = array();
        foreach ($iterator as $file) {
            $filename = $file->getFilename ();
            if ($file->isFile() && $filename[0] != '.' && $filename[0] != '_' &&
                fnmatch($pattern, $filename)) {
                $localPath = $dir . '/' . $filename;
                $source = $baseUrl . $localPath;
                $options = array(
                    'source'    => $source,
                    'filePath'  => $baseDir . $localPath,
                    'localPath' => $localPath
                );
                $result[] = array(
                    'file'      => $source,
                    'options'   => $options
                );
            }
        }
        return $result;
    }
    
    /**
     * Включить все поддиректории указанной директории
     * 
     * @param string $pattern
     * @param integer $doubleStarPos
     * @param string $baseDir
     * @param string $baseUrl
     * @return array
     */
    protected function includeSubdirs($pattern, $doubleStarPos, $baseDir, 
        $baseUrl)
    {
        $dir = trim(substr($pattern, 0, $doubleStarPos), '/');
        $pattern = substr ($pattern, $doubleStarPos + 1);
        $list = array($dir);
        $files = array();
        for ($dir = reset($list); $dir !== false; $dir = next($list)) {
            if (!is_dir($baseDir . $dir)) {
                continue;
            }
            $subdirs = scandir($baseDir . $dir);
            $path = $dir ? $dir . '/' : '';
            for ($j = 0, $count = sizeof($subdirs); $j < $count; $j++) {
                if ($subdirs[$j][0] == '.' || $subdirs[$j][0] == '_') {
                    continue;
                }
                $filename = $baseDir . $path . $subdirs[$j];
                if (is_dir($filename)) {
                    array_push($list, $path . $subdirs[$j]);
                } elseif(fnmatch($pattern, $filename)) {
                    $files[] = array(
                        $baseUrl . $path . $subdirs[$j],
                        $baseDir . $path . $subdirs[$j]
                    );
                }
            }
        }
        $result = array();
        $baseDirLen = strlen($baseDir);
        for ($j = 0, $count = sizeof($files); $j < $count; $j++) {
            $options = array(
                'source'    => $files[$j][0],
                'filePath'  => $files[$j][1],
                'localPath' => substr($files[$j][1], $baseDirLen),
            );
            $result[] = array(
                'file'      => $files[$j][0],
                'options'   => $options
            );
        }
        return $result;
    }
    
    /**
     * Загружает ресурс через паттер директории
     * 
     * @param string $pattern
     * @param string $baseDir
     * @param string $baseDir
     * @param string $type
     * @return array
     */
    protected function loadWithPattern($pattern, $baseDir, $baseUrl)
    {
        $noPack = $pattern[0] == '-';
        $exclude = $pattern[0] == '^';
        $options = array(
            'source'	=> $pattern,
            'nopack'	=> $noPack,
            'exlude'    => $exclude
        );
        if ($noPack || $exclude) {
            $pattern = substr($pattern, 1);
        }
        $results = array();
        $doubleStarPos = strpos($pattern, '**');
        $singleStarPos = strpos($pattern, '*');
        if ($doubleStarPos !== false) {
            $results = $this->includeSubdirs(
                $pattern, $doubleStarPos, $baseDir, $baseUrl
            );
        } elseif ($singleStarPos !== false) {
            $results = $this->includeFiles(
                $pattern, $singleStarPos, $baseDir, $baseUrl
            );
        } else {
            $singleOptions = array(
                'filePath'  => $baseDir . $pattern,
                'localPath' => $pattern
            );
            $sigleResource = array(
                'file'      => $baseUrl . $pattern,
                'options'   => $singleOptions
            );
            $results = array($sigleResource);
        }
        if (!$results) {
            return array();
        }
        foreach ($results as $i => $item) {
            $results[$i]['options'] = array_merge(
                $item['options'], $options
            );
        }
        return $results;
    }

	/**
	 * Возвращает упаковщик ресурсов для указанного типа.
	 * 
     * @param string $type
	 * @return View_Resource_Packer_Abstract
	 */
	public function packer($type)
	{
		if (!isset($this->packers[$type])) {
			$className = 'View_Resource_Packer_' . ucfirst($type);
            $packer = new $className;
			$this->packers[$type] = $packer;
		}
		return $this->packers[$type];
	}
}