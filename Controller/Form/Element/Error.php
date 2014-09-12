<?php

/**
 * Контроллер вывода ошибок валидации формы
 *
 * @author markov
 */
class Controller_Form_Element_Error extends Controller_Abstract 
{
    /**
     * @Context("loader")
     */
    public function index($form, $elementName, $context)
    {
        $element = $form->element($elementName);
        $path = 'Form/Errors/error';
        if ($form->name) {
            $pathByName = 'Form/' . $form->name . '/Errors/error'; 
            $existsPathByName = $context->loader->findFile(
                $pathByName  . '.tpl', 'Form'
            );
            if ($existsPathByName) {
                $path = $pathByName;
            }
        }
        $this->output->send(array(
            'errors'    => $element->errors,
            'template'  => $path . '.tpl'
        ));
    }
}
