<?php

class Cross_Site_Target extends Model
{

	const DATA_BEGIN_MARK	= '<CSDATABEGIN>';

	const DATA_END_MARK		= '</CSDATAEND>';

	/**
	 *
	 * @param string $name
	 * @return Cross_Site_Target
	 */
	public static function byName ($name)
	{
		return Model_Manager::byQuery (
			__CLASS__,
			Query::instance ()
			->where ('name', $name)
		);
	}

	/**
	 * Моментальная отправка и получение ответа
	 * @param Cross_Site_Message $message
	 * @return Cross_Site_Answer
	 */
	public function singleMessage (Cross_Site_Message $message)
	{
//		$url = "http://www.amazon.com/exec/obidos/search-handle-form/002-5640957-2809605";
		$url = $this->address;
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL, $url); // set url to post to
		curl_setopt ($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
		curl_setopt ($ch, CURLOPT_TIMEOUT, 3); // times out after 4s
		curl_setopt ($ch, CURLOPT_POST, 1); // set POST method
		curl_setopt (
			$ch, CURLOPT_POSTFIELDS,
			implode ('&',
				array (
					'key=' . urlencode ($this->secretKey),
					'data=' . urlencode ($message->field ('data')),
					'coder=' . urlencode ($message->Cross_Site_Data_Coder__id)
				)
			) .
			($this->additionalPost ? '&' . $this->additionalPost : '')
		); // add POST fields
		$start_time = time ();
		$source = curl_exec ($ch); // run the whole process
		$duration = time () - $start_time;
		curl_close ($ch);

		$p = strpos ($source, self::DATA_BEGIN_MARK);
		if ($p !== false)
		{
			$data = substr ($source, $p + strlen (self::DATA_BEGIN_MARK));
		}
		else
		{
			$data = '';
		}

		$answer = new Cross_Site_Answer (array (
			'Cross_Site_Target__id'	=> $this->id,
			'time'					=> date ('Y-m-d H:i:s', $start_time),
			'duration'				=> $duration,
			'code'					=> 0,
			'Cross_Site_Data_Coder__id'	=> $message->Cross_Site_Data_Coder__id,
			'source'					=> $source,
			'data'						=> $data,
			'Cross_Site_Message__id'	=> $message->id
		));

		return $answer->save ();
	}

	/**
	 * Отправка одного сообщения и получение ответа
	 * @param array $data
	 * @return Cross_Site_Answer
	 */
	public function singleMessageEx (array $data)
	{
		$message = new Cross_Site_Message (array (
			'Cross_Site_Target__id'			=> $this->id,
			'time'							=> date ('Y-m-d H:i:s'),
			'Cross_Site_Sending_State__id'	=> Cross_Site_Sending_State::SENDING,
			'stateChangeTime'				=> date ('Y-m-d H:i:s'),
			'data'							=> $data,
			'Cross_Site_Data_Coder__id'		=> $this->Cross_Site_Data_Coder__id
		));

		$answer = $this->singleMessage ($message->save ());

		$message->update (array (
			'Cross_Site_Sending_State__id'	=> Cross_Site_Sending_State::SUCCESS
		));

		return $answer;
	}

}