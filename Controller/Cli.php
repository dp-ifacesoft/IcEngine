<?php

/**
 * Обработчик запросов с консоли
 * 
 * @author morph
 */
class Controller_Cli extends Controller_Abstract
{
    /**
     * Выполняет комманду
     * 
     * @Validator("User_Cli")
     */
    public function index($command, $context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0 && !$user->hasRole('editor')) {
            return;
        }
        echo 'Begining... ';
        $slot = new \Event_Slot_Cli_Error();
        $slot->register('RuntimeError');
        list($controllerData, $actionData) = explode('::', $command);
        $executeParts = explode(').', $actionData);
        $actionName = $executeParts[0];
        $actionParams = null;
        $hasParams = strpos($actionName, '(');
        if ($hasParams !== false) {
            $tmp = $actionName;
            $actionName = substr($actionName, 0, $hasParams);
            $actionParams = '@' . ucfirst($actionName) . 
                substr($tmp, $hasParams) .
                    (count($executeParts) > 1 ? ')' : '');
        }
        $parser = new Annotation_Source_Standart;
        if ($actionParams) {
            $actionParams = reset($parser->parse($actionParams));
        }
        $modifiers = array();
        if (isset($executeParts[1])) {
            array_shift($executeParts);
            $count = count($executeParts);
            foreach ($executeParts as $i => $part) {
                $pos = strpos($part, '(');
                $name = substr($part, 0, $pos);
                $params = substr($part, $pos);
                if ($i < $count - 1) {
                    $params .= ')';
                }
                $modifiers[ucfirst($name)] = reset(
                    $parser->parse('@' . ucfirst($name) . $params
                ));
            }
        }
        echo 'done. ' . PHP_EOL;
        $controllerName = str_replace('\\', '_', $controllerData);
        echo 'Preparing to run "Controller_' . $controllerName . '::' . 
            $actionName . '"... ';
        $actionState = new Controller_Action_State($controllerName, $actionName);
        if ($actionParams) {
            $actionState->setArgs(reset($actionParams));
        }
        echo 'done. ' . PHP_EOL;
        $modifierManager = $this->getService('controllerActionModifierManager');
        if ($modifiers) {
            foreach ($modifiers as $modifierName => $params) {
                $modifier = $modifierManager->get($modifierName);
                $modifier->setArgs(reset($params));
                echo 'Applying "Controller_Action_Modifier_' . $modifierName . 
                    '"... done.' . PHP_EOL;
                $actionState->apply($modifier);
            }
        }
        $beginMt = microtime(true);
        $actionState->run();
        echo 'Running... done.' . PHP_EOL;
        $endMt = microtime(true);
        echo 'Time ellapsed ' . ($endMt - $beginMt) . ' sec.' . PHP_EOL;
    }
}