<?php

/**
 * Слот, задающий заголовок контроллеру
 * 
 * @author morph
 */
class Event_Slot_Title extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $params = $this->getParams();
        $buffer = $params['task']->getTransaction()->buffer();
        if (!empty($buffer['origin']) || !$buffer) {
            return;
        }
        $context = $params['context'];
        $titles = $params['titles'];
        $serviceLocator = $context->getControllerManager()->getServiceLocator();
        $registry = $serviceLocator->getService('registry');
        $pageTitle = $serviceLocator->getService('pageTitle');
        $pageTitleSpecificationManager = $serviceLocator->getService(
            'pageTitleSpecificationManager'
        );
        foreach ($titles as $specificationName => $specificationTitles) {
            $specification = $pageTitleSpecificationManager->get(
                $specificationName
            );
            if (!$specification) {
                return;
            }
            if ($specification->isSatisfiedBy($buffer)) {
                list($pageTitle, $siteTitle) = $pageTitle->compileTitles(
                    $specificationTitles, $buffer
                );
                $registry->set('pageTitle', $pageTitle);
                $registry->set('siteTitle', $siteTitle);
                break;
            }
        }
    }
}