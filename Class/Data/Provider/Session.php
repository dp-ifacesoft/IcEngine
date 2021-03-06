<?php

/**
 * Не блокирующий провадер сессий пхп
 *
 * @author dp
 */
class Data_Provider_Session extends Data_Provider_Abstract
{
    private $maxLifeTime = 86400;

    public function __construct($config = null)
    {
        if (!isset($_SESSION)) {
            if (!empty($config['maxLifeTime'])) {
                $this->maxLifeTime = $config['maxLifeTime'];
            }

            if (isset($config['sessionHandlerClass'])) {
                /** @var SessionHandlerInterface $sessionHandlerClass */
                $sessionHandlerClass = $config['sessionHandlerClass'];
                $sessionHandler = new $sessionHandlerClass($this->maxLifeTime);
                session_set_save_handler($sessionHandler);
            }

            session_start();
        }
    }


    public function getSessionId()
    {
        return session_id();
    }

    public function get($key, $plain = false)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function set($key, $value, $expiration = 0, $tags = array())
    {
        $_SESSION[$key] = $value;
    }

    public function delete($keys, $time = 0, $setDeleted = false)
    {
        if (isset($_SESSION[$keys])) {
            unset($_SESSION[$keys]);
        }
    }

    public function destroy()
    {
        session_destroy();
    }

    public function getMaxLifeTime()
    {
        return $this->maxLifeTime;
    }

    public function __destruct()
    {
//        session_write_close();
    }
}