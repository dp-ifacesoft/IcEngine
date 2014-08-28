<?php

/**
 * Тестовый контроллер, досутп для админов, никому не мешает, иногда нужен
 *
 * @author Apostle
 */
class Controller_Test extends Controller_Abstract
{
    /**
     * @Route('/test/')
     * @Role('admin')
     * @Template("null")
     */
    public function index($context)
    {
        //whatever you want
    }
}
