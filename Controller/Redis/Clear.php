<?php

/**
 * Контроллер для управления кэшем редиса.
 *
 * @author Илья Колесников, Юрий Шведов
 */
class Controller_Redis_Clear extends Controller_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array(
		// Роли, имеющие доступ
		'access_roles'		=> array('admin', 'editor', 'cli'),
		// Провайдеры, которые будут игнорироваться при очищение
		'ignore_providers'	=> array('user_session', 'Session_Manager',
			'temp_content'),
		// Обработчики провайдеров, которые будут чиститься
		'provider_names'	=> array('Redis')
	);

	/**
	 * Очистка контента, не затрагивающего сессии пользователей.
	 * Будут очищены результаты запросов, конфиги, виджеты.
     *
     * @Template(null)
     * @Validator("User_Cli")
	 */
	public function clearContent($context)
	{
		$cleared = 0;
		$ignored = array();
		$indexes = array();
		$configManager = $this->getService('configManager');
		$config = $configManager->get('Data_Provider_Manager');
		$ignoreProviders = (array) $this->config()->ignore_providers
			->__toArray();;
		foreach ($config as $name => $provider) {
			if (in_array($name, $ignoreProviders)) {
				$ignored[] = $name;
				continue;
			}
			$indexes[] = array (
				'title'		=> $provider->dscr,
				'name'		=> $name,
				'prefix'	=> $provider['params']['prefix']
			);
		}
		$dataProviderManager = $this->getService('dataProviderManager');
		foreach ($indexes as $index) {
			$provider = $dataProviderManager->get($index['name']);
			if (!$provider) {
				continue;
			}
			++$cleared;
			for ($i = 0; $i < 3; ++$i) {
				$provider->deleteByPattern('');
			}
		}
		$this->output->send(array(
			'cleared'	=> $cleared,
			'ignored'	=> $ignored
		));
	}

	/**
	 * Вывод формы очистики
	 */
	public function index ()
	{
		$breadCrumb = $this->getService('breadCrumb');
		$breadCrumb->append('Очистка кэша', null);
		if (!$this->_checkAccess()) {
			return $this->replaceAction('Error', 'accessDenied');
		}
		$indexes = array ();
		$configManager = $this->getService('configManager');
		$config = $configManager->get('Data_Provider_Manager');
		$providerNames = (array) $this->config()->provider_names->__toArray();
		foreach ($config as $name => $provider) {
			if (!in_array($provider->provider, $providerNames)) {
				continue;
			}
			$indexes[] = array(
				'title'		=> $provider->dscr,
				'index'		=> $name . ':' . $provider->params->prefix
			);
		}
		$isAdmin = 0;
		$user = $this->getService('user')->getCurrent();
		if ($user && $user->hasRole('admin')) {
			$isAdmin = 1;
		}
		$this->output->send(array(
			'isAdmin'		=> $isAdmin,
			'indexes'		=> $indexes,
			'controllers'	=> $this->config()->controller_actions
		));
	}
}