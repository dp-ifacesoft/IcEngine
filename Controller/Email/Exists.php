<?php

/**
 * Контроллер проверяет существует ли уже пользователь с заданным email'ом
 *
 * @author neon
 */
class Controller_Email_Exists extends Controller_Abstract
{
    /**
     * Непосредственная проверка
     *
     * @param string $email
     */
    public function index($email)
    {
        $this->task->setTemplate(null);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $userQuerySelect = $queryBuilder
            ->select('id')
            ->from('User')
            ->where('email', $email);
        $userId = (int) $dds->execute($userQuerySelect)->getResult()->asValue();
        $isExists = false;
        if ($userId > 0 ) {
            $isExists = true;
        }
        $this->output->send(array(
            'data'  => array(
                'isExists'  => $isExists
            )
        ));
    }
}