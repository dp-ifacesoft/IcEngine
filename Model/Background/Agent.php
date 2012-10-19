<?php
/**
 *
 * @desc Фоновый агент
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Background_Agent extends Model_Factory
{

//	/**
//	 * Запуск фонового процесса
//	 * @param string $class
//	 * 		Вызываемый класс
//	 * @param string $method
//	 * 		Вызываемый метод
//	 * @param mixed $params
//	 * @param integer $sec_per_iteration
//	 * @param boolean $immediately
//	 * @return Background_Agent
//	 */
//	public static function create ($class, $method, $params,
//	    $sec_per_iteration = 10)
//	{
//
//		$process = new Background_Agent (array (
//			'startTime'			=> date ('Y-m-d H:i:s'),
//			'lastActiveTime'	=> date ('Y-m-d H:i:s'),
//			'stopTime'			=> '2000-01-01 00:00:00',
//			'callingClass'		=> $class,
//		    'callingMethod'		=> $method,
//			'iterationStarted'	=> 0,
//			'iterationFinished'	=> 0,
//			'secPerIteration'	=> $sec_per_iteration,
//			'stopFlag'			=> 0,
//			'params'			=> json_encode ($params),
//			'Background_Agent_State__id'	=> Background_Agent_State::NONE,
//		    'Background_Agent_Type__id'		=> Background_Agent_Type::CMD
//		));
//
//		$process->save ();
//
//		return $process;
//	}
//
//	/**
//	 * Рабочий процесс.
//	 */
//	public function process ()
//	{
//
//		if (
//			$this->Background_Agent_State__id != Background_Agent_State::NONE &&
//			$this->Background_Agent_State__id != Background_Agent_State::WAITING
//		)
//		{
//			return false;
//		}
//
//		if ($this->realStopFlag ())
//		{
//			$this->update (array (
//				'lastActiveTime'				=> date ('Y-m-d H:i:s'),
//				'Background_Agent_State__id'	=> Background_Agent_State::STOPED
//			));
//			return;
//		}
//		$this->update (array (
//			'lastActiveTime'				=> date ('Y-m-d H:i:s'),
//			'iterationStarted'				=> $this->iterationStarted + 1,
//			'Background_Agent_State__id'	=> Background_Agent_State::PROCESS
//		));
//
//		$class = $this->callingClass;
//		$method = $this->callingMethod;
//
//		$obj = new $class ();
//		$params = (array) json_decode ($this->params, true);
//
//		// Рабочий цикл
//		$start_time = time ();
//		do {
//		    $continue = $obj->{$method} ($params);
//
//		    $this->update (array (
//    			'lastActiveTime'				=> date ('Y-m-d H:i:s'),
//    			'params'						=> json_encode ($params)
//    		));
//
//			if ($continue)
//			{
//    			if ($this->realStopFlag ())
//        		{
//        			$new_state = Background_Agent_State::STOPED;
//        			break;
//        		}
//        		else
//        		{
//        			$new_state = Background_Agent_State::WAITING;
//        		}
//			}
//			else
//			{
//			    $new_state = Background_Agent_State::FINISHED;
//			    break;
//			}
//		} while (time () - $start_time < $this->secPerIteration);
//
//		$this->update (array (
//			'lastActiveTime'				=> date ('Y-m-d H:i:s'),
//			'iterationFinished'				=> $this->iterationFinished + 1,
//			'Background_Agent_State__id'	=> $new_state
//		));
//	}
//
//	/**
//	 * Текущее состояние стоп флага
//	 * @return integer
//	 */
//	public function realStopFlag ()
//	{
//		return DDS::execute (
//			Query::instance ()
//			->select ('stopFlag')
//			->from (__CLASS__)
//			->where ('id=?', $this->id)
//		)->getResult ()->asValue ();
//	}
//
//	/**
//	 * Сброс состояния
//	 */
//	public function resetState ()
//	{
//		$this->update (array (
//			'Background_Agent_State__id'	=> Background_Agent_State::NONE
//		));
//	}
//
//	/**
//	 * Остановка.
//	 * Устанаваливает флаг останова.
//	 */
//	public function stop ()
//	{
//		$this->update (array (
//			'stopFlag'	=> 1
//		));
//	}

}
