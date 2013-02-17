<?php

/**
 * Пустой служебный контроллер, который ничего не делает. Нужен для того,
 * чтобы была возможность перенаправить на него действие, сохранив 
 * транзакцию
 * 
 * @author morph
 */
class Controller_Nope extends Controller_Abstract
{
    /**
     * Пустое действие
     */
    public function nope()
    {
        $this->task->setTemplate(null);
    }
}