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
	 * Проверить права да доступ
	 *
	 * @return boolean
	 */
	public function _checkAccess()
	{
		$user = $this->getService('user')->getCurrent();
		if ($user->key() < 0 || $user->isAdmin()) {
			return true;
		}
		foreach ($this->config()->access_roles as $role) {
			if ($user->hasRole($role)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::_beforeAction()
	 */
	public function _beforeAction($action)
	{
		$dds = $this->getService('dds');
		$query = $this->getService('query');
		$helperDate = $this->getService('helperDate');
		$userService = $this->getService('user');
		$dds->execute(
			$query->insert('Redis_Log')
				->values(array(
					'time'		=> $helperDate->toUnix(),
					'User__id'	=> $userService->id(),
					'action'	=> $action,
					'data'		=> var_export($this->input->receiveAll(), true)
				))
		);
		parent::_beforeAction($action);
	}

	public function clear()
	{
		$this->task->setTemplate(null);
		if (!$this->_checkAccess()) {
			return;
		}
		$indexes = $this->input->receive('index');
		$controllers = $this->input->receive('controllers');
		if (!$indexes && !$controllers) {
			return;
		}
		$dataProviderManager = $this->getService('dataProviderManager');
		$helperHeader = $this->getService('helperHeader');
		if ($controllers) {
			$provider = $dataProviderManager->get('executor');
			$methods = array('callUncached', 'htmlUncached');
			foreach ($controllers as $controller) {
				$controller = explode('::', $controller);
				foreach ($methods as $method) {
					$key = 'Controller_Manager/' . $method . '/' .
						md5($controller);
					$key = urlencode($key);
					$provider->delete($key);
				}
			}
			return $helperHeader->redirect('/caches');
		}
		$ignoreProviders = (array) $this->config()->ignore_providers
			->__toArray();;
		foreach ($indexes as $index) {
			list($name, $pattern) = explode(':', $index);
			$provider = Data_Provider_Manager::get($name);
			if (in_array($name, $ignoreProviders)) {
				continue;
			}
			if (!$provider) {
				continue;
			}
			$provider->conn->clearByPattern($pattern);
		}
		$helperHeader->redirect('/caches');
	}

	/**
	 * Очистка контента, не затрагивающего сессии пользователей.
	 * Будут очищены результаты запросов, конфиги, виджеты.
	 */
	public function clearContent()
	{
		$this->task->setTemplate(null);
		if (!$this->_checkAccess()) {
			return;
		}
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
        $tempContent = $this->getService('tempContent');
        $tc = $tempContent->create(__METHOD__, 'User', $user->key());
		$this->output->send(array(
			'isAdmin'		=> $isAdmin,
			'indexes'		=> $indexes,
            'utcode'        => $tc->key(),
			'controllers'	=> $this->config()->controller_actions
		));
	}
}