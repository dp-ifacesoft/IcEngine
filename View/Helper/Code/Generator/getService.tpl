    /**
     * Get service by name
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }