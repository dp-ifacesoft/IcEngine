<?php

/**
 * Помощник по работе с api
 *
 * @author markov
 * @Service("helperApi")
 */
class Helper_Api extends Helper_Abstract
{
    
    private $passwords = array(
        'wQaWBoK5Y7VcGA2c'
    );
    
    /**
     * Выполняет
     * 
     * @param string $cmd название команды
     * @param string $sig сигнатура для проверки
     * @param array $params данные запроса
     * @return array
     */
    public function execute($cmd, $sig, $params)
    {
        $apiError = $this->getService('apiError');
        $apiCommand = $this->getService('apiCommandManager')
            ->get($cmd);
        $status = Api_Status::OK;
        $response = null;
        $error = 0;
        if (!$apiCommand) {
            $status = Api_Status::ERROR;
            $error = Api_Error::COMMAND_NOT_FOUND;
        } elseif (!$this->accessCheck($cmd, $sig)) {
            $status = Api_Status::ERROR;
            $error = Api_Error::ACCESS_DENIED;
        } else {
            $apiCommand->setParams($params);
            if ($apiCommand->checkParams()) {
                $response = $apiCommand->run();
            } else {
                $status = Api_Status::ERROR;
                $error = Api_Error::NOT_ENOUGH_PARAMS;
            }
        }
        return array(
            'status'            => $status,
            'response'          => $response,
            'error'             => $error,
            'errorDescription'  => $apiError->getErrorDescription($error)
        );
    }
    
    /**
     * Проверяет доступ на получение данных
     * 
     * @param string $cmd название команды
     * @param string $sig сигнатура
     * @return boolean
     */
    public function accessCheck($cmd, $sig)
    {
        foreach ($this->passwords as $password) {
            $sigTmp = md5($cmd . $password);
            if ($sig == $sigTmp) {
                return true;
            }
        }
        return false;
    }
}
