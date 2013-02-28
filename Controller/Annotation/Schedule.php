<?php

/**
 * Контроллер для аннотаций типа "Schedule"
 * 
 * @author morph
 */
class Controller_Annotation_Schedule extends Controller_Abstract
{
    /**
     * Обновить аннотации
     */
    public function update($data)
    {
        $this->task->setTemplate(null);
        if (!$data) {
            return;
        }
        $schedules = array();
        foreach ($data as $controllerAction => $annotationData) {
            $subData = $annotationData['Schedule'];
            $scheduleData = $subData['data'][0];
            $interval = reset($scheduleData);
            $priority = isset($scheduleData['priority'])
                ? $scheduleData['priority'] : 0;
            $schedules[$controllerAction] = array(
                'interval'  => $interval,
                'priority'  => $priority
            );
        }
        $dds = $this->getService('dds');
        $queryBuilder = $this->getService('query');
        $scheduleNames = array_keys($schedules);
        $scheduleQuery = $queryBuilder
            ->select('controllerAction')
            ->from('Schedule');
        $existsScheduleNames = $dds->execute($scheduleQuery)->getResult()
            ->asColumn();
        $addedSchedules = array_diff($scheduleNames, $existsScheduleNames);
        $deletedSchedules = array_diff($existsScheduleNames, $scheduleNames);
        if ($deletedSchedules) {
            $deleteQuery = $queryBuilder
                ->delete()
                ->from('Schedule')
                ->where('controllerAction', $deletedSchedules);
            $dds->execute($deleteQuery);
        }
        if ($addedSchedules) {
            $unitOfWork = $this->getService('unitOfWork');
            foreach ($addedSchedules as $scheduleName) {
                if (!isset($schedules[$scheduleName])) {
                    continue;
                }
                $scheduleData = $schedules[$scheduleName];
                $interval = substr($scheduleData['interval'], 1, -1);
                $multiplier = substr($scheduleData['interval'], -1);
                switch(strtolower($multiplier)) {
                    case 'h': $multiplier = 3600; break;
                    case 'm': $multiplier = 60; break;
                    default:  $multiplier = 1;
                }
                $deltaSec = intval($interval) * $multiplier;
                $insertQuery = $queryBuilder
                    ->insert('Schedule')
                    ->values(array(
                        'controllerAction'  => $scheduleName,
                        'deltaSec'          => $deltaSec,
                        'lastTs'            => 0,
                        'priority'          => $scheduleData['priority']
                    ));
                $unitOfWork->push($insertQuery);
            }
            $unitOfWork->flush();
        }
    }
}