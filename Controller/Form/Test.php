<?php

/**
 * тестовый контроллер
 *
 * @author markov
 */
class Controller_Form_Test extends Controller_Abstract
{
    /**
     * @Context("formBuilder")
     * @Route('/testform/')
     */
    public function test($context)
    {
        $form = $context->formBuilder
            ->setName('testForm')
            ->setAttribute('action', '/test/')
            ->setAttribute('enctype', 'text/plain')
            ->add('author', 'text')
            ->add('name', 'text')
                ->getForm();
        $this->output->send(array(
            'form'  => $form
        ));
    }
}
