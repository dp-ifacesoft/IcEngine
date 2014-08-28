<?php

/**
 * Упаковщик Css ресурсов представления
 *
 * @author goorus, morph
 */
class View_Resource_Packer_Css extends View_Resource_Packer_Abstract
{
	/**
	 * Импортируемые стили.
	 *
     * @var array
	 */
	protected $imports = array();

	/**
	 * Домен второго уровня
	 *
     * @var string
	 */
	protected $domain = 'localhost';

	/**
	 * Шаблоны имен доменов
	 *
     * @var Objective
	 */
	protected $domains;

	/**
	 * Последний использованный домен.
	 *
     * @var integer
	 */
	protected $last = 0;

	/**
	 * Расширения конфига
	 *
     * @var array
	 */
	protected $configExt = array(
		// Домены
//		'domains'	=> array (
//			'img1.{$domain}{$url}',
//			'img2.{$domain}{$url}',
//			'img3.{$domain}{$url}',
//			'img4.{$domain}{$url}',
//			'img5.{$domain}{$url}',
//			'img6.{$domain}{$url}',
//			'img7.{$domain}{$url}',
//			'img8.{$domain}{$url}',
//			'img9.{$domain}{$url}'
//		)
	);

	/**
	 * Сформированные адреса изображений.
	 * Необходимо чтобы на одно изображние не получалось несколько ссылок.
	 *
     * @var array <String>
	 */
	protected $formedUrls = array();

	/**
	 * Конструктор
	 */
	public function __construct()
	{
        $locator = IcEngine::serviceLocator();
        $helperUri = $locator->getService('helperUri');
		$this->domain = $helperUri->mainDomain();
		$this->config = array_merge(
			$this->config,
			$this->configExt
		);
		$this->domains = $this->config()->domains;
	}

	/**
	 * Callback для preg_replace вырезания @import.
	 *
     * @param array $matches
	 * @return string
	 */
	public function excludeImport(array $matches)
	{
		if (strncmp($matches[1], '/', 1) == 0) {
			$this->imports[] = $matches[0];
		} else {
			$this->imports[] =
				'@import "' . $this->currentResource->urlPath .
                $matches[1] . '";';
		}
		return '';
	}

	/**
	 * Callback для preg_replace замены путей к изображениям.
	 *
     * @param array $matches
	 * @return string
	 */
	public function replaceUrl(array $matches)
	{
		if (substr($matches[1], 0, 5) == 'data:') {
			return 'url(' . $matches[1] . ')';
		} elseif (substr($matches[1], 0, 5) == 'http:') {
			return 'url(' . $matches[1] . ')';
		}
		if (substr($matches[1], 0, 1) == '/') {
			$url = $matches[1];
		} else {
			$url = $this->currentResource->urlPath . $matches[1];
		}
		if (isset($this->formedUrls[$url])) {
			$url = $this->formedUrls[$url];
		} elseif (
			substr($url, 0, 1) == '/' &&
			$this->domains && count($this->_domains) // Objective, не массив
		) {
			$this->last++;
			if ($this->last >= count($this->domains)) {
				$this->last = 0;
			}
			$this->formedUrls[$url] = 'http://' . str_replace(
				array('{$domain}', '{$url}'),
				array($this->domain, $url),
				$this->domains[$this->last]
			);
			$url = $this->formedUrls[$url];
		}
		return 'url("' . $url . '")';
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Resource_Packer_Abstract::compile()
	 */
	public function compile(array $packages)
	{
		return $this->compileFilePrefix() .
			implode("\n", $this->imports) . "\n" .
			implode("\n", $packages);
	}

	/**
	 * @inheritdoc
	 */
	public function packOne(View_Resource $resource)
	{
        $config = $this->config();
		$resource->urlPath = dirname($resource->href) . '/';
		if ($config->item_prefix && isset($resource->filePath)) {
			$prefix = strtr ($config->item_prefix, array (
                '{$source}' => $resource->filePath,
                '{$src}'	=> $resource->localPath,
            ));
		} else {
			$prefix = '';
		}
		$replacedStyle = preg_replace_callback(
			'/url\\([\'"]?(.*?)[\'"]?\\)/i',
			array($this, 'replaceUrl'),
			$resource->content()
		);
		$excludedStyle = preg_replace_callback(
			'/@import\\s*[\'"]?(.*?)[\'"]?\\s*;/i',
			array ($this, 'excludeImport'),
			$replacedStyle
		);
		$trimmedStyle = preg_replace(
            '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '',
            $excludedStyle
        );
		$style = str_replace(
            array("\r", "\t", '@CHARSET "UTF-8";'), '', $trimmedStyle
        );
		do {
			$length = strlen($style);
			$style = str_replace('  ', ' ', $style);
		} while (strlen($style) != $length);
		return $prefix . $style . $config->item_postfix;
	}
}