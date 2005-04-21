<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/shm.aw,v 2.5 2005/04/21 08:33:32 kristo Exp $
// shm.aw - shared memory functions here
// all variable keys should be SHM_BASE + number

define("SHM_BASE", 666);
define("SHM_KEYLIST", SHM_BASE);

class shm
{
	function shm()
	{
		// read keylist
		$s_id = shm_attach(SHM_KEYLIST);
		$this->keylist = @shm_get_var($s_id, SHM_KEYLIST);
		shm_detach($s_id);

		if (!is_array($this->keylist))
		{
			$this->keylist = array();
		}
	}

	////
	// !saves a variable in shared memory
	function put($key, $value)
	{
		$this->keylist[$key] = $key;
		$s_id = shm_attach($key, max(strlen($value),1000), 0600);
		shm_put_var($s_id, $key, $value);
		shm_detach($s_id);
		$this->update_keylist();
	}

	////
	// !returns the variable value for key $key from shared memory
	function get($key)
	{
		if (isset($this->keylist[$key]))
		{
			$s_id = shm_attach($key);
			$var = shm_get_var($s_id, $key);
			shm_detach($s_id);
			return $var;
		}
		return false;
	}

	////
	// !removes a variable with key $key from shared memory
	function remove($key)
	{
		if (isset($this->keylist[$key]))
		{
			$s_id = shm_attach($key);
			shm_remove_var($s_id, $key);
			shm_detach($s_id);
			shm_remove($key);
			unset($this->keylist[$key]);
			$this->update_keylist();
			return true;
		}
		return false;
	}

	////
	// !removes all variables from shared memory, except the keylist
	function remove_all()
	{
		foreach($this->keylist as $key)
		{
			$this->remove($key);
		}
	}

	////
	// !removes the keylist from shared memory 
	// warning - after doing this, you no longer have access to variables stored previously, 
	// so always do this after remove_all()
	function remove_keylist()
	{
		$s_id = @shm_attach(SHM_KEYLIST);
		@shm_remove_var($s_id, SHM_KEYLIST);
		shm_detach($s_id);
		@shm_remove(SHM_KEYLIST);
		$this->keylist = array();
	}

	////
	// !dumps all variables in shared memory to the user
	function dump_all_vars()
	{
		echo "keylist = <pre>", var_dump($this->keylist),"</pre> <br />";
		foreach($this->keylist as $key)
		{
			echo "key = $key , var = <pre>", var_dump($this->get($key)),"</pre> <br />";
		}
	}

	////
	// !internal use - updates the keylist in shm
	function update_keylist()
	{
		// 15k for your key-list should be enough for everybody, dammet!
		$s_id = shm_attach(SHM_KEYLIST, 15000, 0600);
		shm_put_var($s_id, SHM_KEYLIST, $this->keylist);
		shm_detach($s_id);
	}
}

?>
