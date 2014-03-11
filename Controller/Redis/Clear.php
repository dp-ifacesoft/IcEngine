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
	protected $_config = array(
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
		$user = User::getCurrent();
		if (!$user || $user->isAdmin()) {
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
		DDS::execute (
			Query::instance ()
				->insert('Redis_Log')
				->values (array (
					'time'		=> Helper_Date::toUnix (),
					'User__id'	=> User::id (),
					'action'	=> $action,
					'data'		=> var_export($this->_input->receiveAll (), true)
				))
		);
		parent::_beforeAction ($action);
	}

	public function clear()
	{
		$this->_task->setTemplate(null);
		if (!$this->_checkAccess()) {
			return;
		}
		$indexes = $this->_input->receive('index');
		$controllers = $this->_input->receive('controllers');
		if (!$indexes && !$controllers) {
			return;
		}
		if ($controllers) {
			$provider = Data_Provider_Manager::get('executor');
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
			return Helper_Header::redirect('/caches');
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
		Helper_Header::redirect('/caches');
	}

	/**
	 * Очистка контента, не затрагивающего сессии пользователей.
	 * Будут очищены результаты запросов, конфиги, виджеты.
	 */
	public function clearContent()
	{
		$this->_task->setTemplate(null);
		if (!$this->_checkAccess()) {
			return;
		}
		$cleared = 0;
		$ignored = array();
		$indexes = array();
		$config = Config_Manager::get('Data_Provider_Manager');
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
		foreach ($indexes as $index) {
			$provider = Data_Provider_Manager::get ($index['name']);
			if (!$provider) {
				continue;
			}
			++$cleared;
			for ($i = 0; $i < 3; ++$i) {
				$provider->deleteByPattern('');
			}
		}
		$this->_output->send(array(
			'cleared'	=> $cleared,
			'ignored'	=> $ignored
		));
	}

	/**
	 * Вывод формы очистики
	 */
	public function index ()
	{
		Bread_Crumb::append('Очистка кэша', null);
		if (!$this->_checkAccess()) {
			return $this->replaceAction('Error', 'accessDenied');
		}
		$indexes = array ();
		$config = Config_Manager::get('Data_Provider_Manager');
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
		$user = User::getCurrent();
		if ($user && $user->hasRole('admin'))
		{
			$isAdmin = 1;
		}
		$this->_output->send (array(
			'isAdmin'		=> $isAdmin,
			'indexes'		=> $indexes,
			'controllers'	=> $this->config()->controller_actions
		));
	}

}
