<?php

/**
 * Планировщик
 *
 * @author morph
 */
class Controller_Schedule extends Controller_Abstract
{
    protected $config = array(
        'processLimit'  => 3
    );
    
    /**
     * Выполнить задания
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperSchedule", "collectionManager")
     */
    public function index($context)
    {
        $config = $this->config();
        $schedules = $context->collectionManager->create('Schedule')
            ->addOptions(
                array(
                    'name'  => '::Order_Desc',
                    'field' => 'priority'
                ),
                array(
                    'name'  => '::Order_Asc',
                    'field' => 'lastDate'
                )
            );
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
            if ($inProcessCount >= $config->processLimit) {
                break;
            }
            if ($schedule['inProcess']) {
                continue;
            }
            $scheduleTs = $schedule['lastTs'] + $schedule['deltaSec'];
            if ($scheduleTs > $currentTs) {
                continue;
            }
            $lastDate = $helperDate->toUnix();
            if ($schedule['hasExectTime']) {
                $lastDate = substr($lastDate, 0 , strlen($lastDate) - 8) . 
                    substr($schedule['exectTime'], 
                        strlen($schedule['exectTime']) - 8 , 8
                    );
                $currentTs = (new DateTime($lastDate))->getTimestamp();
            }
            $inProcessCount ++;
            $schedule->update(array(
                'lastTs'    => $currentTs,
                'lastDate'  => $lastDate,
                'inProcess' => 1
            ));
            echo $schedule['controllerAction'] . PHP_EOL;
            $params = $schedule['paramsJson'] 
                ? $context->helperSchedule->get($schedule['paramsJson']) : null;
            exec(
                './ic ' . $schedule['controllerAction'] . 
                ($params ? ' ' . $params : '')
            );
            $schedule->update(array(
                'inProcess' => 0
            ));
        }
    }
}