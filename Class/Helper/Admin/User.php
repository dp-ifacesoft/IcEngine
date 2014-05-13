<?php

/**
 * Описание User
 *
 * @author Apostle
 * @Service("helperAdminUser")
 */
class Helper_Admin_User extends Helper_Abstract 
{
    
    
    /**
     * добавить сотрудника в конфиг Config/Authorization/Login/Password/Sms
     * @param string $login
     * @param string $password
     * @param string $phone
     * @param integer $active
     * @return boolean
     */
    public function addToConfig($login, $password, $phone, $active = true) 
    {
       $configManager = $this->serviceLocator->getService('configManager');
       $config = $configManager->get('Authorization_Login_Password_Sms');
       $configArray = $config->__toArray();
       $usersArray = $configArray['users'];
       $md5password = 'Md5://' . $password;
       $newUser = array($login => array(
           'active' => $active,
           'password' => $md5password,
           'phone' => $phone)
           );
       $newUsers = array_merge($usersArray, $newUser);
       $configArray['users'] = $newUsers;
       $filename = IcEngine::root() . '/Ice/Config/Authorization/Login/Password/Sms.php';
       $this->arrayToFile($configArray, $filename);
 }
 
    /**
     * Удалить пользователя из конфига
     * @param string $login логин
     * @return boolean
     */
    public function removeFromConfig($login) 
    {
       $configManager = $this->serviceLocator->getService('configManager');
       $config = $configManager->get('Authorization_Login_Password_Sms');
       $configArray = $config->__toArray();
       $usersArray = $configArray['users'];
       if (!isset($usersArray[$login])) {
           return;
       } 
       unset($usersArray[$login]);
       $configArray['users'] = $usersArray;
       $filename = IcEngine::root() . '/Ice/Config/Authorization/Login/Password/Sms.php';
       $this->arrayToFile($configArray, $filename);
    }
 
    /**
     * Записать массив данных в сам файл
     * @param array $array данные
     * @param string $filename путь к файлу
     * @return boolean
     */
    public function arrayToFile($array, $filename) 
    {
        if (!is_writable($filename) || !$handle = fopen($filename, 'a')) {
            return false;
        }
        ob_start();
        echo '<?php' . PHP_EOL;
        echo '/**' . PHP_EOL;
        echo '*' . PHP_EOL;
        echo '* @desc Конфиг для авторизации по емейлу, паролю и СМС' . PHP_EOL;
        echo '* @var array' . PHP_EOL;
        echo '*' . PHP_EOL;
        echo '*/' . PHP_EOL;
        echo 'return ';
        var_export($array);
        echo ';';
        $data = ob_get_clean();
        $fp = fopen($filename, "w");
        fwrite($fp, $data);
        fclose($fp); 
    } 
}
