<?php

/**
 * Планировщик
 *
 * @author morph
 */
class Controller_Schedule extends Controller_Abstract
{
    /**
     * Выполнить задания
     */
    public function index($context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0) {
            return;
        }
        $schedules = $context->collectionManager->create('Schedule')
            ->addOptions(array(
                'name'  => '::Order_Desc',
                'field' => 'priority'
            ));
        $currentTs = time();
        $helperDate = $this->getService('helperDate');
        foreach ($schedules as $schedule) {
            $scheduleTs = $schedule['lastTs'] + $schedule['deltaSec'];
            if ($scheduleTs > $currentTs) {
                continue;
            }
            $schedule->update(array(
                'lastTs'    => $currentTs
            ));
            echo $schedule['controllerAction'] . PHP_EOL;
            exec('./ice ' . $schedule['controllerAction']);
        }
    }
}