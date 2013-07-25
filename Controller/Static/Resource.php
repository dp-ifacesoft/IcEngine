<?php

/**
 * Упаковщик статичных ресурсов
 *
 * @author morph
 */
class Controller_Static_Resource extends Controller_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array(
		'params'	=> array(
			'cityId'
		),
        'staticDir' => 'cache/static/'
	);

	/**
	 * Упаковать
     * 
     * @Template(null)
	 */
	public function pack($context)
	{
		$resourceCollection = $context->collectionManager->create(
			'Static_Resource'
		)->addOptions(
			'::Active',
			array(
				'name'	=> '::Order_Asc',
				'field'	=> 'sort'
			)
		);
		$params = array();
		foreach ($this->config()->params as $paramName) {
			$params[$paramName] = $this->input->receive($paramName);
		}
		foreach ($resourceCollection as $resource) {
            echo 'Name: ' . $resource->name . PHP_EOL;
			$context->controllerManager->call(
				$resource->name, 'toFile', $params
			);
		}
	}

	/**
	 * Пересобрать статику
     *
     * @Context("dataProviderManager")
     * @Ajax
     * @Template(null)
     * @Validator("User_CliOrEditor")
	 */
	public function recache($context)
	{
		$provider = $context->dataProviderManager->get('Static');
        $context->controllerManager->call('Static_Resource', 'pack');
        $context->controllerManager->call('View_Resource', 'index');
        $provider->set('last', time());
        $config = $this->config();
        if (!$config->staticDir) { 
            return;
        }
        $dir = IcEngine::root() . trim($config->staticDir) . '/';
        $files = scandir($dir);
        if (!$files) {
            return;
        }
        foreach ($files as $filename) {
            if ($filename[0] == '.') {
                continue;
            }
            unlink($dir . $filename);
        }
	}
}