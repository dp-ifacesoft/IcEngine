<?php

class Data_Provider_FileCache extends Data_Provider_Abstract
{
	
	/**
	 * Признак для разбиения ключей по директориям
	 * @var string
	 */
	public $keyDelim = '\\';
	
	public $path = 'cache/';
	public $tempPrefix = '_temp_';
	
	/**
	 * Очищает указанную директорию.
	 * Все файлы из директории $dir и всех поддиректорий будут удалены.
	 * @param string $dir
	 */
	public function _clearDir ($dir)
	{
		$objs = glob ($dir . "/*");
		if ($objs)
		{
			foreach ($objs as $obj)
			{
				is_dir ($obj) ? $this->_clearDir ($obj) : unlink ($obj);
			}
		}
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param integet $expiration=0
	 * @param array $tags=array()
	 * @return boolean
	 */
	public function add ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('add', $key, $expiration);
		}
		
		$file = $this->makePath ($key);
		
		if (file_exists ($file))
		{
			return false;
		}
		
		$data = array (
			'v'	=> $value,
		);
		
		if ($expiration > 0)
		{
			$data ['e'] = $expiration + time ();
		}
		
		if (!empty ($tags))
		{
			$data ['t'] = $this->getTags ($tags);
		}
		
		$data = json_encode ($data);
		
		$fh = fopen ($file, 'xb');
		if (!$fh)
		{
			return false;
		}
		set_file_buffer ($fh, 0);
		
		fwrite ($fh, $data, strlen ($data));
		fclose ($fh);
		
		return true;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public function append ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('append', $key);
		}
		
		$file = $this->makePath ($key);
		
		if (!file_exists ($file))
		{  
			return false;
		}
		
		$fh = fopen ($file, 'w+b');
		if (!$fh)
		{
			return false;
		}
		set_file_buffer ($fh, 0);
		
		$size = filesize ($file);
		if ($size)
		{
			$data = fread ($fh, $size);
			$data = json_decode ($data, true);
			
			if (
				isset($data ['e']) && 
				$data ['e'] > 0 && $data ['e'] < time ()
			)
			{
				$data ['v'] = $value;
			}
			else
			{
				$data ['v'] .= $value;
			}
		}
		else
		{
			$data = array (
				'v'	=> $value
			);
		}
		$data = json_encode ($data);
		fseek ($fh, 0, SEEK_SET);
		fwrite ($fh, $data, strlen ($data));
		
		fclose ($fh);
	}
	
	/**
	 * 
	 * @param string $key
	 * @param integer $value=1
	 * @return boolean
	 */
	public function decrement ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('decrement', $key);
		}
		
		$file = $this->makePath ($key);
		
		if (!file_exists ($file))
		{  
			return false;
		}
		
		$fh = fopen ($file, 'w+b');
		if (!$fh)
		{
			return false;
		}
		set_file_buffer ($fh, 0);
		
		$size = filesize ($file);
		if ($size)
		{
			$data = fread ($fh, $size);
			$data = json_decode ($data, true);
			$data['v'] -= $value;
		}
		else
		{
			$data = array(
				'v' => -1
			);
		}
		$data = json_encode ($data);
		fseek ($fh, 0, SEEK_SET);
		fwrite ($fh, $data, strlen ($data));
		ftruncate ($fh, strlen($data));
		
		fclose ($fh);
	}
	
	/**
	 * 
	 * @param string|array $keys
	 * @param integer $time
	 * @param boolean $set_deleted=false
	 * @return interger
	 */
	public function delete ($keys, $time = 0, $set_deleted = false)
	{
		$keys = (array) $keys;
		
		if ($this->tracer)
		{
			$this->tracer->add ('delete', implode (',', $keys), $time);
		}
		
		$delete_count = 0;
		
		foreach ($keys as $key)
		{
			if (is_array ($key))
			{
				$key = $key [0];
			}
			
			if (isset ($this->locks [$key]))
			{
				unset ($this->locks [$key]);
			}
			
			if ($set_deleted)
			{
				$this->set ($this->prefixDeleted . $key, time ());
			}
			
			$file = $this->makePath ($key);
			if (file_exists ($file))
			{
				unlink ($file);
			}
			$delete_count++;
		}
			
		return $delete_count;
	}
	
	/**
	 * 
	 * @param integer $delay=0
	 * @return integer
	 */
	public function flush ($delay = 0)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('flush', $delay);
		}
		
		$delete_count = 0;
		
		// Данные
		$this->_clearDir ($this->path);
		
		return $delete_count;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param boolean $plain=false
	 * @return boolean|string
	 */
	public function  get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}
		
		$file = $this->makePath ($key);
		
		if (!file_exists ($file))
		{  
			return false;
		}
		
		$fh = fopen ($file, 'rb');
		if (!$fh)
		{
			return false;
		}
		if (!flock ($fh, LOCK_SH | LOCK_NB))	// Захват файла на чтение
		{
			return false;
		}
		$data = fread ($fh, filesize ($file));
		flock ($fh, LOCK_UN);
		fclose ($fh);
		
		$data = json_decode ($data, true);
		
		if (!is_array ($data) || !isset ($data ['v']))
		{
			return false;
		}
		elseif (
			isset ($data ['e']) && 
			$data ['e'] > 0 && $data ['e'] < time ()
		)
		{
			unlink ($file);
			return false;
		}
		elseif (
			isset ($data ['t']) &&
			!$this->checkTags ($data ['t'])
		)
		{
			unlink ($file);
			return false;
		}
		
		return $data ['v'];
	}
	
	/**
     * @method getStats
     * @return array
     * @description get statistics.
 	 */
	public function getStats ()
	{
		return '';
	}
	
	/**
     * Increment specified key with value.
     * @param string $key incrementing key.
     * @param integer $value=1 value to increment.
	 */
	public function increment ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('increment', $key);
		}
		
		$file = $this->makePath ($key);
		
		if (!file_exists ($file))
		{  
			return false;
		}
		
		$fh = fopen ($file, 'w+b');
		if (!$fh)
		{
			return false;
		}
		set_file_buffer ($fh, 0);
		
		$size = filesize ($file);
		if ($size)
		{
			$data = fread ($fh, $size);
			$data = json_decode ($data, true);
		
			$data ['v'] += $value;
		}
		else
		{
			$data = array (
				'v'	=> 1
			);
		}
		$data = json_encode ($data);
		fseek ($fh, 0, SEEK_SET);
		fwrite ($fh, $data, strlen ($data));
		ftruncate ($fh, strlen ($data));
		
		fclose ($fh);
	}
	
	/**
     * Get keys by wildcard
     * @param string $pattern
     * @param string $server=NULL
	 */
	public function keys ($pattern, $server = NULL)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('keys', $pattern);
		}
		
		$mask = $this->makePath ($pattern);
		$mask = str_replace ('%2A', '*', $mask);
		$files = glob ($mask);
		
		$l = strlen ($this->path);
		
		$keys = array ();
		
		$es = '_' . DIRECTORY_SEPARATOR;
		foreach ($files as $file)
		{
			$file = urldecode (substr ($file, $l));
			if (substr ($file, 0, 2) == $es)
			{
				$keys [] = substr ($file, 2);
			}
			else
			{
				$keys [] = str_replace (
					DIRECTORY_SEPARATOR,
					$this->keyDelim,
					$file
				);
			}
		}
		
		return $keys;
	}
	
	/**
	 * 
	 * @param string $key
	 * @return string
	 */
	public function makePath ($key)
	{
		$p = strpos ($key, $this->keyDelim);
		if ($p > 0 && $p < strlen ($key) - 1)
		{
			$path = $this->path . substr ($key, 0, $p);
			$file = urlencode (substr ($key, $p + 1));
			if (!is_dir ($path))
			{
				if (!mkdir ($path, 0777, true))
				{
					trigger_error ('Unable to mkdir: ' . $path, E_USER_WARNING);
				}
			}
		}
		else
		{
			$path = $this->path . '_';
			$file = urlencode ($key);
			if (!is_dir ($path))
			{
				if (!mkdir ($path, 0777, true))
				{
					trigger_error ('Unable to mkdir: ' . $path, E_USER_WARNING);
				}
			}
		}
		
		return $path . DIRECTORY_SEPARATOR . $file;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public function prepend ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('prepend', $key);
		}
		
		$file = $this->makePath ($key);
		
		if (!file_exists ($file))
		{  
			return false;
		}
		
		$fh = fopen ($file, 'w+b');
		if (!$fh)
		{
			return false;
		}
        set_file_buffer ($fh, 0);
		
		$size = filesize ($file);
		if ($size)
		{
			$data = fread ($fh, $size);
			$data = json_decode ($data, true);
			
			if (
				isset ($data['e']) && 
				$data ['e'] > 0 && $data ['e'] < time ()
			)
			{
				$data ['v'] = $value;
			}
			else
			{
				$data ['v'] = $value . $data ['v'];
			}
		}
		else
		{
			$data = array (
				'v'	=> $value
			);
		}
		$data = json_encode ($data);
		fseek ($fh, 0, SEEK_SET);
		fwrite ($fh, $data, strlen ($data));

		fclose ($fh);
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiration=0
	 * @param array $tags=array()
	 * @return boolean
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key, $expiration);
		}
		
		//$tmp_file = $this->makePath ($this->tempPrefix . $key . uniqid() . rand(0, 1000));
		$dst_file = $this->makePath ($key);
		
		$data = array (
			'v'	=> $value
		);
		
		if ($expiration > 0)
		{
			$data ['e'] = $expiration + time ();
		}
		
		if (!empty ($tags))
		{
			$data ['t'] = $this->getTags ($tags);
		}
		
		$data = json_encode ($data);
		$fh = fopen ($dst_file, 'wb');
		if (!$fh)
		{
			return false;
		}
		set_file_buffer ($fh, 0);
		
		fwrite ($fh, $data, strlen ($data));
		fclose ($fh);
		//file_put_contents ($tmp_file, json_encode ($data));
		// fix for windows if file exists
		//copy ($tmp_file, $dst_file);
		//unlink ($tmp_file);
		//return rename($tmp_file, $dst_file);
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public function setOption ($key, $value)
	{
		switch ($key)
		{
			case 'path':
				$this->path = $value;
				break;
			case 'temp_prefix':
				$this->tempPrefix = $value;
				break;
			default:
				trigger_error ('Unkown option: ' . $key, E_NOTICE);
				return false;
		}
		return true;
	}
	
}
