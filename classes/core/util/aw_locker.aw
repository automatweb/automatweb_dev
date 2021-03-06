<?php

/**
	@attrib maintainer=kristo
**/

class aw_locker
{
	const SEM_ID = 15000;
	const SHM_KEY = 15000;
	const SHM_VAR = 15;

	const BOUNDARY_ALL = 1;		// locks are applied globally - any attempt to access this object from anywhere (even via global oids) will block. this is VERY SLOW and currently not implemented
	const BOUNDARY_SERVER = 2;	// locks only apply within the current server - objects can be accessed from other hosts. this is relatively quick.
	const BOUNDARY_PROCESS = 3;	// locks only apply to the current process. any other processes or servers will not see them. this is the quickest. 


	const LOCK_FULL = 1;		// LOCK_FULL means that all calls to the object will block/except until the lock is freed
	const LOCK_READ_ONLY = 2;	// LOCK_READ_ONLY only means that write calls will block/except until the lock is freed

	const WAIT_BLOCK = 1;		// if a lock is on, the call will block until the lock is released
	const WAIT_EXCEPTION = 2;	// if a lock is on, the call will throw an exception and thus return immediately. 

	// whenever locks are released, the object in all other processes will immediately be reloaded from the database to ensure that changes are propagated properly.


	const OPERATION_READ = 1;
	const OPERATION_WRITE = 2;

	private static $instance = null;
	private static $inprocess_locks = array();
	private static $sem_id = null;
	private static $shm_id = null;

	private function __construct()
	{
		;
	}

	/** singleton instance maker
		@attrib api=1
	**/
	public static function instance()
	{
		if (self::$instance === null)
		{
			self::$instance = new aw_locker();
		}
		return self::$instance;
	}

	/** locks the specified resource
		@attrib api=1 params=pos

		@param class required type=string
			Class of the resource to lock

		@param id required type=int
			Id of the resource to lock

		@param type required type=int
			One of aw_locker::LOCK_FULL (blocks reading AND writing) or aw_locker::LOCK_READ_ONLY (blocks just writing)

		@param boundary required type=int
			Sets the lock boundary - aw_locker::BOUNDARY_ALL (global) or aw_locker::BOUNDARY_SERVER (just this server) aw_locker::BOUNDARY_PROCESS (just this process on this server) 

		@param wait_type required type=int
			Default waiting type on the lock - aw_locker::WAIT_BLOCK (blocks until the lock is released) or aw_locker::WAIT_EXCEPTION (throws exception aw_lock_exception if the lock is held)

		@comment
			Locks the resource with the given identifier (class and id) . The BOUNDARY_ALL is currently the same as BOUNDARY_SERVER - sorry about that. 

		@examples
			process one:
				aw_set_exec_time(AW_LONG_PROCESS);
				$locker = aw_locker::instance();
				$locker->lock("object", 12, aw_locker::LOCK_READ_ONLY, aw_locker::BOUNDARY_SERVER, aw_locker::WAIT_BLOCK);

				sleep(5);

				$locker->unlock("object", 12);
			
			process two:
				try {
					aw_locker::try_operation("object", 12, aw_locker::OPERATION_READ, aw_locker::WAIT_EXCEPTION);
				}
				catch(aw_lock_exception $e)
				{
					echo "Resource is locked!";
				}
				echo "Resource available!";

			if run in parallel, the second process throws an exception
	**/
	public function lock($class, $id, $type, $boundary, $wait_type)
	{
		return;
		// FIXME
		if ("win32" === aw_ini_get("server.platform")) { return; }
		self::_block();
		if (!self::_is_locked($class, $id))
		{
			$ident  = self::get_ident($class, $id);
			switch($boundary)
			{
				case self::BOUNDARY_ALL:
				case self::BOUNDARY_SERVER:
					if (self::$shm_id === null)
					{
						self::$shm_id = shm_attach(self::SHM_KEY, 50 * 1024, 0666);
					}
					$locks = shm_get_var(self::$shm_id, self::SHM_VAR);
					if (!is_array($locks))
					{		
						$locks = array();
					}
					// pid is fine here, cause locks are released at end of process anyway
					$locks[$ident] = array($type, $wait_type, $boundary, getmypid());
					shm_put_var(self::$shm_id, self::SHM_VAR, $locks);
					break;

				case self::BOUNDARY_PROCESS:
					self::$inprocess_locks[$ident] = array($type, $wait_type, $boundary);
					break;
			}
		}
		self::_unblock();
	}

	/** Unlocks the specified resource
		@attrib api=1 params=pos

		@param class required type=string
			Class of the resource to unlock

		@param id required type=int
			Id of the resource to unlock
	**/
	public function unlock($class, $id)
	{
return;
		// FIXME
		if ("win32" === aw_ini_get("server.platform")) { return; }

		self::_block();
		if (($data = self::_is_locked($class, $id, true)))
		{
			$ident  = self::get_ident($class, $id);

			$boundary = $data[2];

			switch($boundary)
			{
				default:
				case self::BOUNDARY_ALL:
				case self::BOUNDARY_SERVER:
					if (self::$shm_id === null)
					{
						self::$shm_id = shm_attach(self::SHM_KEY, 50 * 1024, 0666);
					}
					$locks = shm_get_var(self::$shm_id, self::SHM_VAR);
					if (!is_array($locks))
					{		
						$locks = array();
					}
					unset($locks[$ident]);
					shm_put_var(self::$shm_id, self::SHM_VAR, $locks);
					break;

				case self::BOUNDARY_PROCESS:
					unset(self::$inprocess_locks[$ident]);
					break;
			}
		}
		self::_unblock();
	}

	/** Checks for a lock on the given resource
		@attrib api=1 params=pos

		@param class required type=string
			Class of the resource to check

		@param id required type=int
			Id of the resource to check
	
		@param try_type optional type=int
			Type of operation to try, one of aw_locker::OPERATION_READ or aw_locker::OPERATION_WRITE, defaults to read

		@param wait_type optional type=int
			Waiting type for the check, one of aw_locker::WAIT_BLOCK (blocks until lock is released) aw_locker::WAIT_EXCEPTION (throws exception if lock is held)

	**/
	public static function try_operation($class, $id, $try_type = self::OPERATION_READ, $wait_type = null)
	{
return;
		// FIXME
		if ("win32" === aw_ini_get("server.platform")) { return; }
		
		self::_block();
		if (($data = self::_is_locked($class, $id)))
		{
			$type = $data[0];
			if ($wait_type === null)
			{
				$wait_type = $data[1];
			}

			if ($type == self::LOCK_READ_ONLY && $try_type == self::OPERATION_READ)
			{
				self::_unblock();
				return;
			}
			
			switch($wait_type)
			{
				case self::WAIT_BLOCK:
					self::_unblock();
					do {
						usleep(500);
						self::_block();
						$data = self::_is_locked($class, $id);
						self::_unblock();
					} while($data);
					break;

				case self::WAIT_EXCEPTION:
					self::_unblock();
					throw new aw_lock_exception($class, $id, $try_type, $type, $wait_type);
					break;
			}
		}
		else
		{
			self::_unblock();
		}
	}

	private static function _is_locked($class, $id, $all_proc = false)
	{
		// go over all boundaries and check them, fastest first
		$ident = self::get_ident($class,$id);

		// BOUNDARY_PROCESS
		if (isset(self::$inprocess_locks[$ident]))
		{
			return self::$inprocess_locks[$ident];
		}

		// BOUNDARY_SERVER
		if (self::$shm_id === null)
		{
			self::$shm_id = shm_attach(self::SHM_KEY, 50 * 1024, 0666);
		}
		// this will generate a warning. once per server reboot. and I can't help it. 
		$locks = shm_get_var(self::$shm_id, self::SHM_VAR);
		if (!is_array($locks))
		{
			$locks = array();
			shm_put_var(self::$shm_id, self::SHM_VAR, $locks);
		}
		// process locks are only for other processes, not the locker
		if (isset($locks[$ident]) && ($all_proc || $locks[$ident][3] != getmypid()))
		{
			return $locks[$ident];
		}

		// BOUNDARY_ALL
		// TODO: implement

		return false;
	}

	private static function get_ident($class, $id)
	{
		return $class."::".$id;
	}

	private static function _block()
	{
		if (!self::$sem_id)
		{
			self::$sem_id = sem_get(self::SEM_ID);
		}
		sem_acquire(self::$sem_id);
	}

	private static function _unblock()
	{
		sem_release(self::$sem_id);
	}
}


class aw_lock_exception extends aw_exception
{
	public $data = array();

	public function __construct($class, $id, $try_type, $type, $wait_type)
	{
		$this->data = array(
			$class,
			$id, 
			$try_type,
			$type,
			$wait_type
		);
	}
}
