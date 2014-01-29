<?php

/**
 * Планировщик
 *
 * @author morph
 */
class Controller_Schedule extends Controller_Abstract
{
    protected $config = array(
        'processLimit'  => 1
    );
    
    /**
     * Выполнить задания
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperSchedule")
     */
    public function index($context)
    {
        $config = $this->config();
        $schedules = $context->collectionManager->create('Schedule')
            ->addOptions(array(
                'name'  => '::Order_Desc',
                'field' => 'priority'
            ));
        $currentTs = time();
        $helperDate = $this->getService('helperDate');
        $inProcessCount = 0;
        foreach ($schedules as $schedule) {
            if ($schedule['inProcess']) {
                $inProcessCount ++;
            }
        }
        if ($inProcessCount >= $config->processLimit) {
            return false;
        }
        foreach ($schedules as $schedule) {
            if ($schedule['inProcess']) {
                continue;
            }
            $scheduleTs = $schedule['lastTs'] + $schedule['deltaSec'];
            if ($scheduleTs > $currentTs) {
                continue;
            }
            $schedule->update(array(
                'lastTs'    => $currentTs,
                'lastDate'  => $helperDate->toUnix(),
                'inProcess' => 1
            ));
            echo $schedule['controllerAction'] . PHP_EOL;
            $params = $schedule['paramsJson'] 
                ? $context->helperSchedule->get($schedule['paramsJson']) : null;
            exec(
                './ice ' . $schedule['controllerAction'] . 
                ($params ? ' ' . $params : '')
            );
            $schedule->update(array(
                'inProcess' => 0
            ));
        }
    }
}