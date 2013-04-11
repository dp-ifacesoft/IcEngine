<?php
/**
 * 
 * @desc Контроллер для яндекс карт
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Yandex_Map extends Controller_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $config = array (
		// Ключ по умолчанию
		'default_key'		=> 'AN61kk0BAAAAmfUFHQIAviXJjepM1vwUHiRbviRvdAPQ1NMAAAAAAAAAAADerIzEKE8_wbCnBoKFcC4TrqVRCg==',
		// Ключи для различных доменов
		'domains'			=> array (
			// $pattern	=> $key
			'/.*forguest\\.grs/'	=> 'AK6GpU0BAAAA0p1BFwIAcBCubCS1dft85kAI-THTx4i475oAAAAAAAAAAABYpvprBqy52GepuLwQlMVPg7xDpg=='
		),
		
		// Скрывать ошибки яндекс карт (будут выводиться в консоль)
		'hide_errors'		=> true,
		
		// loadByRequire в запросе скрипта
		'load_by_require'	=> false
	);
	
	/**
	 * @desc Возврщает ключ для домена 
	 * @param string $domain Домен. Если null, будет взят из $_SERVER ['HTTP_HOST']
	 */
	protected function _getKey ($domain = null)
	{
		if (!$domain)
		{
			$domain = $_SERVER ['HTTP_HOST'];
		}
		
		$config = $this->config ();
		
		if ($config ['domains'])
		{
			foreach ($config ['domains'] as $pattern => $key)
			{
				if (preg_match ($pattern, $domain))
				{
					return $key;
				}
			}
		}
		
		return $config ['default_key'];
	}
	
	/**
	 * @desc Добавляет строку подключения скрипта яндекс карт.
	 */
	public function includeScript ($mode = '')
	{
		list (
			$domain,
			$hide_errors,
			$load_by_require,
			$wizard,
            $mode
            ) = $this->input->receive (
			'domain',
			'hide_errors',
			'load_by_require',
			'wizard',
            'mode'
        );
		
		$key = $this->_getKey ($domain);
		
		$config = $this->config ();
		
		$this->output->send (array (
            'mode'				=> $mode,
			'hide_errors'		=> 
				is_null ($hide_errors) ?
					$config ['hide_errors'] :
					$hide_errors,
			'key'				=> $key,
			'load_by_require'	=> 
				is_null ($load_by_require) ?
					$config ['load_by_require'] :
					$load_by_require,
			'wizard'			=> $wizard
		));
	}
	
	/**
	 * @desc Добавляет скрипт, который позволит позже вызвать метод
	 * Controller_Yandex_Map.lazyLoad js контроллера для инициализации
	 * карты.
	 */
	public function lazyLoad ()
	{
		static $loaded = false;
		if ($loaded)
		{
			$this->task->setTemplate (null);
		}
		else
		{
			$loaded = true;
			$this->includeScript ();
		}
	}
	
}