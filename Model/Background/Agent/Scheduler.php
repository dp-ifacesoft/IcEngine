<?php

class Background_Agent_Scheduler extends Background_Agent_Abstract
{
	public function _finish ()
	{

	}

	/**
	 * @desc Расчитать период в секундах
	 * @param decimal $num
	 * @param string $verb
	 * @return integer
	 */
	private function _calcPeriod ($num, $verb)
	{
		$verbs = array (
			'sec'	=> 1,
			'min'	=> 60,
			'hour'	=> 3600,
			'day'	=> 86400,
			'week'	=> 604800,
			'month'	=> 2592000,
			'year'	=> 31536000
		);

		return (int) ($verbs [$verb] * $num);
	}

	public function _process ()
	{
		$task_collection = Model_Collection_Manager::byQuery (
			'Task',
			Query::instance ()
				->where ('active', 1)
		);

		foreach ($task_collection as $task)
		{
			$delta = 0;

			$parts = explode (' ', $task->period);

			for ($i = 0, $icount = sizeof ($parts); $i < $icount; $i+=2)
			{
				$delta += $this->_calcPeriod ($parts [$i], $parts [$i + 1]);
			}

			if (!$task->lastTime)
			{
				$task->lastTime = time ();
			}

			if ($task->lastTime + $delta >= time ())
			{
				$task->update (array (
					'lastTime'	=> time ()
				));

				$queue = new Task_Queue (array (
					'Task__id'		=> $task->key (),
					'createdAt'		=> Helper_Date::toUnix (),
					'finishedAt'	=> Helper_Date::NULL_DATE,
					'processed'		=> 0
				));

				$queue->save ();
			}
		}
	}

	public function _start ()
	{

	}
}