<?php

/*
@classinfo maintainer=voldemar
*/

class class_index
{
	const INDEX_DIR = "/files/class_index/";
	const CLASS_DIR = "/classes/";
	const LOCAL_CLASS_DIR = "/files/classes/";
	const LOCAL_CLASS_PREFIX = "_aw_local_class__"; // local class names in form OBJ_LOCAL_CLASS_PREFIX . $class_obj_id
	const UPDATE_EXEC_TIMELIMIT = 300;
	const CL_NAME_MAXLEN = 1024;

	// update collects implemented interfaces in this, so we can update the interface definitions with the list of classes that implement that interface
	private static $implements_interface = array();

	/**
	@attrib api=1 params=pos
	@param full_update optional type=bool
		Update all definitions regardless if class files modified and perform maintenance. Default false
	@returns void
	@comment
		Updates entire class index. Reads all files in class directory and parses them, looking for php class definitions.
	**/
	public static function update($full_update = true)
	{
		return;
		// ...
		$max_execution_time_prev_val = ini_get("max_execution_time");
		set_time_limit(self::UPDATE_EXEC_TIMELIMIT);

		// update
		self::$implements_interface = array();
		$found_classes = self::_update("", "", $full_update);
		self::update_one_file(aw_ini_get("basedir")."/class_index.aw", $found_classes, $full_update, "../");
		self::update_one_file(aw_ini_get("basedir")."/init.aw", $found_classes, $full_update, "../");

		if ($full_update)
		{
			self::write_implements_interface();
			self::clean_up($found_classes);
		}

		// restore normality
		set_time_limit($max_execution_time_prev_val);
	}

	private static function _update($class_dir = "", $path = "", $full_update = false)
	{
		$time = time();

		if (empty($class_dir))
		{
			$class_dir = aw_ini_get("basedir") . self::CLASS_DIR;
		}

		$index_dir = aw_ini_get("basedir") . self::INDEX_DIR;

		// make index directory if not found
		if (!is_dir($index_dir))
		{
			$ret = mkdir($index_dir, 0777);

			if (!$ret)
			{
				$e = new awex_clidx_filesys("Failed to create index directory.");
				$e->clidx_file = $index_dir;
				$e->clidx_op = "mkdir";
				throw $e;
			}

			$ret = chmod($index_dir, 0777);

			if (!$ret)
			{
				$e = new awex_clidx_filesys("Failed to change index directory permissions.");
				$e->clidx_file = $index_dir;
				$e->clidx_op = "mkdir";
				throw $e;
			}

		}

		if (!is_dir($class_dir))
		{
			throw new awex_clidx("Class directory doesn't exist.");
		}

		// scan all files in given class directory for php class definitions
		$found_classes = array(); // names of all found classes/ifaces

		if ($handle = opendir($class_dir))
		{
			$non_dirs = array(".", "..", "CVS");
			$ext = aw_ini_get("ext");

			if (empty($ext))
			{
				$ext_len = self::CL_NAME_MAXLEN;
			}
			else
			{
				$ext = "." . $ext;
				$ext_len = - strlen($ext);
			}

			// these files are ignored under class directory
			$cl_dir_tmp = aw_ini_get("basedir") . self::CLASS_DIR;
			$ignore_files = array($cl_dir_tmp . "core/fastcall_base" . $ext);

			while (($file = readdir($handle)) !== false)
			{
				$class_file = $class_dir . $file;

				if ("file" === @filetype($class_file) and strrchr($file, ".") === $ext and !in_array($class_file, $ignore_files))
				{ // process only applicable code files
					self::update_one_file($class_file, &$found_classes, $full_update, $path);
				}
				elseif ("dir" === @filetype($class_file) and !in_array($file, $non_dirs))
				{
					$found_classes = array_merge(self::_update($class_dir . $file . "/", $path . $file . "/", $full_update), $found_classes);
				}
			}

			closedir($handle);
		}
		else
		{
			$e = new awex_clidx_filesys("Couldn't open class directory.");
			$e->clidx_file = $class_dir;
			$e->clidx_op = "opendir";
			throw $e;
		}

		return $found_classes;
	}

	private static function update_one_file($class_file, &$found_classes, $full_update, $path)
	{
		$time = time();
		$index_dir = aw_ini_get("basedir") . self::INDEX_DIR;
		$ext = aw_ini_get("ext");

		if (empty($ext))
		{
			$ext_len = self::CL_NAME_MAXLEN;
		}
		else
		{
			$ext = "." . $ext;
			$ext_len = - strlen($ext);
		}

		$cl_handle = null; // class file resource handle
		// parse code
		$tmp = token_get_all(file_get_contents($class_file));
		$type = "";

		foreach ($tmp as $token)
		{
			if (T_CLASS === $token[0])
			{
				$type = "class";
			}
			elseif (T_INTERFACE === $token[0])
			{
				$type = "interface";
			}
			elseif (T_STRING === $token[0] and ("class" === $type or "interface" === $type))
			{
				if (is_resource($cl_handle) and !empty($class_dfn))
				{ // write previous class/iface dfn file
					fwrite($cl_handle, serialize($class_dfn));
					fclose($cl_handle);
				}

				$modified = filemtime($class_file);
				$class_path = $path . substr(basename($class_file), 0, $ext_len);// relative path + file without extension
				$class_name = $token[1];
				$class_dfn_file = $index_dir . $class_name . $ext;

				// look for redeclared classes
				if (in_array($class_name, $found_classes) and "core/locale" !== substr($class_path, 0, 11))
				{
					if (!is_readable($class_dfn_file))
					{
						$e = new awex_clidx_filesys("Can't read redeclared class definition file '" . $class_dfn_file . "'.");
						$e->clidx_cl_name = $class_name;
						$e->clidx_file = $class_dfn_file;
						$e->clidx_op = "is_readable";
						throw $e;
					}

					$class_dfn = unserialize(file_get_contents($class_dfn_file));
					$e = new awex_clidx_double_dfn("Duplicate definition of '" . $class_name . "' in '" . $class_dfn["file"] . "' and '" . $class_path . "'.");
					$e->clidx_cl_name = $class_name;
					$e->clidx_path1 = $class_dfn["file"];
					$e->clidx_path2 = $class_path;
					throw $e;
				}

				if (!$full_update and is_readable($class_dfn_file))
				{ // try to read old data for class/iface found
					$class_dfn = unserialize(file_get_contents($class_dfn_file));
				}
				else
				{
					$class_dfn = array();
				}

				if (
					!isset($class_dfn["last_update"]) or
					false === $modified or
					$class_dfn["last_update"] < $modified or
					$class_dfn["file"] !== $class_path
				)
				{ // previous definition not found or class/iface modified
					// new definition
					$class_dfn = array(
						"file" => $class_path,
						"clidx_version" => 4, // to comply with changes to class index format
						"last_update" => $time,
						"type" => $type
					);

					// open index file for this class/iface
					$cl_handle = @fopen($class_dfn_file, "w");
					$ret = chmod($class_dfn_file, 0666);

					if (!$ret)
					{
						$e = new awex_clidx_filesys("Failed to change file permissions for " . $class_dfn_file . ".");
						$e->clidx_file = $class_dfn_file;
						$e->clidx_op = "mkdir";
						throw $e;
					}

					// aquire lock
					if (!flock($cl_handle, LOCK_EX))
					{
						$e = new awex_clidx_filesys("Unable to update class index for '" . $class_file . "'. Failed to aquire lock.");
						$e->clidx_cl_name = $class_name;
						$e->clidx_file = $class_dfn_file;
						$e->clidx_op = "fopen";
						throw $e;
					}
				}
				else
				{
					$class_dfn = array();
				}

				$found_classes[] = $class_name;
				$type = "";
			}
			elseif (T_EXTENDS === $token[0])
			{
				$type = "extends";
			}
			elseif (T_STRING === $token[0] and "extends" === $type and !empty($class_dfn))
			{ // 'extends' always comes right after class name therefore variables are still set.
				$class_parent = $token[1];
				$class_dfn["extends"] = $class_parent;
				$type = "";
			}
			elseif (T_IMPLEMENTS === $token[0])
			{
				$type = "implements";
			}
			elseif (T_STRING === $token[0] and "implements" === $type and !empty($class_dfn))
			{ // 'implements' always comes right after class name therefore variables are still set.
				$interface = $token[1];
				$class_dfn["implements"] = $interface;
				self::$implements_interface[$interface][] = $class_name;
				// can't empty type, because we can have multiple implements
				//$type = "";
			}
			else
			if ($token == "{")
			{
				// this comes after all implements and things are done in the class
				$type = "";
			}
		}

		if (is_resource($cl_handle) and !empty($class_dfn))
		{ // write last class dfn file
			fwrite($cl_handle, serialize($class_dfn));
			fclose($cl_handle);
		}
	}

	/**
	@attrib api=1 params=pos
	@param name required type=string
		Class name
	@returns string Class definition file absolute path
	**/
	public static function get_file_by_name($name)
	{
		$ext = aw_ini_get("ext");

		// determine if class is aw class or local
		if (0 === strpos($name, self::LOCAL_CLASS_PREFIX))
		{
			// load local class
			$class_file = aw_ini_get("site_basedir") . self::LOCAL_CLASS_DIR . $name . "." . $ext;

			if (!is_readable($class_dfn_file))
			{
				$e = new awex_clidx_filesys("Local class definition not found or not readable.");
				$e->clidx_cl_name = $name;
				$e->clidx_file = $class_dfn_file;
				$e->clidx_op = "is_readable";
				throw $e;
			}
		}
		else
		{
			// try existing index
			$class_dfn_file = aw_ini_get("basedir") . self::INDEX_DIR . $name . "." . $ext;
			$class_dir = aw_ini_get("basedir") . self::CLASS_DIR;

			if (!is_readable($class_dfn_file))
			{
				// update index and try again
				self::update();

				if (!is_readable($class_dfn_file))
				{
					$e = new awex_clidx_filesys("'" . $name . "' class/interface definition not found.");
					$e->clidx_cl_name = $name;
					$e->clidx_file = $class_dfn_file;
					$e->clidx_op = "is_readable";
					throw $e;
				}
			}

			$class_dfn = unserialize(file_get_contents($class_dfn_file));

			if (1 >= (int) $class_dfn["last_update"])
			{ // in case definition is corrupt or ...
				self::update();
				$class_dfn = unserialize(file_get_contents($class_dfn_file));
			}

			// load aw class dfn
			$class_file = $class_dir . $class_dfn["file"] . "." . $ext;

			if (!is_readable($class_file))
			{
				// class file may have changed, update index.
				self::update();

				if (!is_readable($class_dfn_file))
				{
					$e = new awex_clidx_filesys("'" . $name . "' class/interface definition not found.");
					$e->clidx_cl_name = $name;
					$e->clidx_file = $class_dfn_file;
					$e->clidx_op = "is_readable";
					throw $e;
				}

				$class_dfn = unserialize(file_get_contents($class_dfn_file));
				$class_file = $class_dir . $class_dfn["file"] . "." . $ext;

				if (!is_readable($class_file))
				{
					$e = new awex_clidx_filesys("Class file not found. Update created an index file with false data.");
					$e->clidx_cl_name = $name;
					$e->clidx_file = $class_file;
					$e->clidx_op = "is_readable";
					throw $e;
				}
			}
		}

		return $class_file;
	}

	/**
	@attrib api=1 params=pos
	@param name required type=string
		Class name
	@param parent required type=string
		Parent class name
	@returns boolean
	@comment Checks whether class specified by $name extends $parent
	**/
	public static function is_extension_of($name, $parent)
	{
		if (!is_string($name) or !is_string($parent))
		{
			return false;
		}

		$parents = array();

		do
		{
			$class_dfn_file = aw_ini_get("basedir") . self::INDEX_DIR . $name . "." . aw_ini_get("ext");

			if (!is_readable($class_dfn_file))
			{
				self::update();

				if (!is_readable($class_dfn_file))
				{
					$e = new awex_clidx_filesys("Class definition not found or not readable.");
					$e->clidx_cl_name = $name;
					$e->clidx_file = $class_dfn_file;
					$e->clidx_op = "is_readable";
					throw $e;
				}
			}

			$class_dfn = unserialize(file_get_contents($class_dfn_file));

			if (empty($class_dfn["clidx_version"])) // clidx_version must be >=1, earlier formats don't have 'extends' parameter.
			{
				self::update();
				$class_dfn = unserialize(file_get_contents($class_dfn_file));
			}

			$parents[] = $class_dfn["extends"];
			$name = isset($class_dfn["extends"]) ? $class_dfn["extends"] : false;
		}
		while ($name or $name === $parent);

		return (bool) in_array($parent, $parents);
	}

	private function clean_up($classes)
	{
		$index_dir = aw_ini_get("basedir") . self::INDEX_DIR;
		$ext_len = strlen(aw_ini_get("ext"));
		$ext_len = empty($ext_len) ? self::CL_NAME_MAXLEN :  (- 1 - $ext_len);

		if ($handle = opendir($index_dir))
		{
			while (($cl_dfn_file = readdir($handle)) !== false)
			{
				$file = $index_dir . $cl_dfn_file;

				if ("file" === @filetype($file) and !in_array(substr($cl_dfn_file, 0, $ext_len), $classes))
				{
					$deleted = unlink($file);

					if (!$deleted)
					{
						$e = new awex_clidx_filesys("Couldn't delete redundant file in class index");
						$e->clidx_file = $file;
						$e->clidx_op = "unlink";
						throw $e;
					}
				}
			}

			closedir($handle);
		}
		else
		{
			$e = new awex_clidx_filesys("Couldn't open index directory");
			$e->clidx_file = $index_dir;
			$e->clidx_op = "opendir";
			throw $e;
		}
	}

	/** Returns a list of classes that implement a particular interface
		@attrib api=1 params=pos

		@param interface required type=string
			The name of the interface to search for

		@returns
			array { class_name_with_folder => class_name } for all classes that implement the given interface
	**/
	public static function get_classes_by_interface($interface)
	{
		$index_dir = aw_ini_get("basedir") . self::INDEX_DIR;
		$if_file = $index_dir.$interface.".".aw_ini_get("ext");

		if (!file_exists($if_file))
		{
			self::update(true);
			if (!file_exists($if_file))
			{
				$e = new awex_clidx_filesys("Could not open interface $if_file class index.");
				$e->clidx_file = $if_file;
				$e->clidx_op = "file_exists";
				throw $e;
			}
		}
		$fc = unserialize(file_get_contents($if_file));

		if ($fc["type"] !== "interface")
		{
			$e = new awex_clidx("get_classes_by_interface($interface): the requested interface is not an interface!");
			$e->clidx_cl_name = $interface;
			throw $e;
		}
		return safe_array($fc["implemented_by"]);
	}

	private static function write_implements_interface()
	{
		$index_dir = aw_ini_get("basedir") . self::INDEX_DIR;
		foreach(self::$implements_interface as $if_name => $implemented_by)
		{
			$if_file = $index_dir.$if_name.".".aw_ini_get("ext");

			$fc = unserialize(file_get_contents($if_file));
			if (!is_array($fc))
			{
				$e = new awex_clidx_filesys("Could not open interface $if_name class index.");
				$e->clidx_file = $if_name;
				$e->clidx_op = "file_get_contents";
				throw $e;
			}

			$fc["implemented_by"] = $implemented_by;
			$f = fopen($if_file, "w");
			fwrite($f, serialize($fc));
			fclose($f);
		}
	}
}

class awex_clidx extends aw_exception
{
	public $clidx_cl_name;
}

class awex_clidx_filesys extends awex_clidx
{
	public $clidx_file;
	public $clidx_op;
}

class awex_clidx_double_dfn extends awex_clidx
{
	public $clidx_path1;
	public $clidx_path2;
}

class awex_clidx_lock extends aw_exception {}

?>
