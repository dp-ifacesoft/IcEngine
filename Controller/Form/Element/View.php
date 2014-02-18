<?php

/**
 * Отображение элемента формы
 *
 * @author markov
 */
class Controller_Form_Element_View extends Controller_Abstract
{
    /**
     * Отображает элемент формы
     * 
     * @Context("loader")
     */
    public function index($form, $elementName, $context)
    {
        $element = $form->element($elementName);
        $elementType = $element->getType();
        $path = 'Form/General/' . $elementType;
        if ($form->name) {
            $pathByName = 'Form/' . $form->name . '/' . $element->name; 
            $existsPathByName = $context->loader->findFile(
                $pathByName  . '.tpl', 'Form'
            );
            if ($existsPathByName) {
                $path = $pathByName;
            } else {
                $pathByType = 'Form/' . $form->name . '/General/'. $elementType;
                $pathExistsByType = $context->loader->findFile(
                    $pathByType  . '.tpl', 'Form'
                );
                if ($pathExistsByType) {
                    $path = $pathByType;
                }
            }
        }
        $this->task->setTemplate($path);
        $this->output->send(array(
            'element'   => $element
        ));
    }
}
