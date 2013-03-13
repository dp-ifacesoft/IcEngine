<?php

/**
 * Вывод результатов профайлинга
 *
 * @author morph
 */
class Controller_Tracer extends Controller_Abstract
{
	/**
	 * Вывод результатов
	 */
	public function index()
	{
		$maxMemory = ini_get('memory_limit');
		$user = $this->getService('user')->getCurrent();
        $request = $this->getService('request');
		if (!$user->isAdmin() || !$request->get('TRACER') || !Tracer::$enabled) {
			//return $this->_task->setTemplate(null);
		}
		$lowQueryVector = Tracer::getLowQueryVector();
		$lowQueryCount = count($lowQueryVector);
		$lowQueryTime = 0;
		foreach ($lowQueryVector as $query) {
			$lowQueryTime += $query[1];
		}
		$sessions = Tracer::getSessions();
		array_pop($sessions);
        $resourceManager = $this->getService('resourceManager');
		$this->output->send(array(
			'totalTime'				=> Tracer::getTotalTime(),
			'maxMemory'				=> $maxMemory,
			'bootstrapTime'			=> Tracer::getBootstrapTime(),
			'dispatcherTime'		=> Tracer::getDispatcherTime(),
			'frontControllerTime'	=> Tracer::getFrontControllerTime(),
			'renderTime'			=> Tracer::getRenderTime(),
			'loadedClassCount'		=> Tracer::getLoadedClassCount(),
			'lowQueryCount'			=> $lowQueryCount,
			'lowQueryVector'		=> $lowQueryVector,
			'lowQueryTime'			=> $lowQueryTime,
			'selectQueryCount'		=> Tracer::getSelectQueryCount(),
			'selectQueryTime'		=> Tracer::getSelectQueryTime(),
			'updateQueryCount'		=> Tracer::getUpdateQueryCount(),
			'updateQueryTime'		=> Tracer::getUpdateQueryTime(),
			'insertQueryCount'		=> Tracer::getInsertQueryCount(),
			'insertQueryTime'		=> Tracer::getInsertQueryTime(),
			'deleteQueryCount'		=> Tracer::getDeleteQueryCount(),
			'deleteQueryTime'		=> Tracer::getDeleteQueryTime(),
			'redisGetCount'			=> Tracer::getRedisGetCount(),
			'redisSetCount'			=> Tracer::getRedisSetCount(),
			'redisKeyCount'			=> Tracer::getRedisKeyCount(),
			'redisDeleteCount'		=> Tracer::getRedisDeleteCount(),
			'redisGetTime'			=> Tracer::getRedisGetTime(),
			'redisSetTime'			=> Tracer::getRedisSetTime(),
			'redisKeyTime'			=> Tracer::getRedisKeyTime(),
			'redisDeleteTime'		=> Tracer::getRedisDeleteTime(),
			'totalModelCount'		=> Tracer::getTotalModelCount(),
			'controllerCount'		=> Tracer::getControllerCount(),
			'cachedControllerCount'	=> Tracer::getCachedControllerCount(),
			'cachedSelectQueryCount'	=> Tracer::getCachedSelectQueryCount(),
			'sessions'					=> $sessions,
			'bootstrapInitDb'				=> Tracer::getBootstrapInitDbTime(),
			'bootstrapInitAttributeManager'	=> 
                Tracer::getBootstrapInitAttributeManagerTime(),
			'bootstrapInitModelScheme'		=> 
                Tracer::getBootstrapInitModelSchemeTime(),
			'bootstrapInitModelManager'		=> 
                Tracer::getBootstrapInitModelManagerTime(),
			'bootstrapInitAcl'				=> 
                Tracer::getBootstrapInitAclTime(),
			'bootstrapInitUser'				=> 
                Tracer::getBootstrapInitUserTime(),
			'bootstrapInitUserSession'		=> 
                Tracer::getBootstrapInitUserSessionTime(),
			'routingTime'					=> Tracer::getRoutingTime(),
            'allQueryVector'                => Tracer::getAllQueryVector(),
            'models'                        => $resourceManager->getByType(
                'Model'
            )
		));
	}
}