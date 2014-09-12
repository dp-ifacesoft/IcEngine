<?php

/**
 * Created by PhpStorm.
 * User: dp
 * Date: 21.04.14
 * Time: 15:44
 */
class Session
{
    const DATA_PROVIDER_KEY = 'session';

    /** @var Data_Provider_Session */
    private static $_dataProvider = null;

    public static function getId()
    {
        return self::getDataProvider()->getSessionId();
    }

    public static function getMaxLifeTime() {
        return self::getDataProvider()->getMaxLifeTime();
    }

    public static function get($key)
    {
        return self::getDataProvider()->get($key);
    }

    public static function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                self::set($k, $value);
            }

            return;
        }

        self::getDataProvider()->set($key, $value);
    }

    public static function delete($key) {
        self::getDataProvider()->delete($key);
    }

    public static function destroy() {
        self::getDataProvider()->destroy();
    }

    /**
     * @return Data_Provider_Session
     */
    private static function getDataProvider()
    {
        if (self::$_dataProvider !== null) {
            return self::$_dataProvider;
        }

        self::$_dataProvider = IcEngine::dataProviderManager()->get(self::DATA_PROVIDER_KEY);

        return self::$_dataProvider;
    }
} 