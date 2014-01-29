<?php

/**
 * Помощник для вывода в нужной форме слова, на основание числа перед ним
 *
 * @author neon
 * @Service("helperPlural")
 */
class Helper_Plural
{
    /**
     * Вывод в нужной форме
     * 
     * @param int $number
     * @param string $forms "день,дня,дней"
     */
    public function plural($number, $forms) 
    {
        $locator = IcEngine::serviceLocator();
        $viewHelperManager = $locator->getService('viewHelperManager');
        return $viewHelperManager->get(
            'Plural',
            array(
                'value'	=> $number,
                'forms'	=> $forms
            )
        );
    }
}