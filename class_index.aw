<?php
/*
@classinfo maintainer=voldemar
*/

class class_index
{
	const INDEX_DIR = "/pagecache/class_index/";
	const CLASS_DIR = "/classes/";
	const LOCAL_CLASS_DIR = "/files/classes/";
	const LOCAL_CLASS_PREFIX = "_aw_local_class__"; // local class names in form OBJ_LOCAL_CLASS_PREFIX . $class_obj_id
	const UPDATE_EXEC_TIMELIMIT = 300;

	/**
	@attrib api=1 params=pos
	@param full_update required type=string
		Update additional info also. Currently only class parent info.
	@returns void
	@comment
		Updates entire class index. Reads all files in class directory and parses them, looking for php class definitions.
	**/
	public static function update($full_update = false)
	{
		self::_update("", "", $full_update);
	}

	private static function _update($class_dir = "", $path = "", $full_update =  false)
	{
		$time = time();
		$max_execution_time_prev_val = ini_get("max_execution_time");
		set_time_limit(self::UPDATE_EXEC_TIMELIMIT);

		if (empty($class_dir))
		{
			$class_dir = aw_ini_get("basedir") . self::CLASS_DIR;
		}

		$index_dir = aw_ini_get("site_basedir") . self::INDEX_DIR;

		// make index directory if not found
		if (!is_dir($index_dir))
		{
			$ret = mkdir($index_dir, 0700);

			if (!$ret)
			{
				throw new awex_clidx("Failed to create index directory.");
			}
		}

		if (!is_dir($class_dir))
		{
			throw new awex_clidx("Class directory doesn't exist.");
		}

		// scan all files in given class directory for php class definitions
		if ($handle = opendir($class_dir))
		{
			$non_dirs = array(".", "..", "CVS");
			$ext = aw_ini_get("ext");
			$ext_len = strlen($ext);

			while (($file = readdir($handle)) !== false)
			{
				$class_file = $class_dir . $file;

				// process only code files
				if ("file" === @filetype($class_file) and strrchr($file, ".") === "." . $ext)
				{
					// parse code
					$tmp = token_get_all(file_get_contents($class_file));
					$next = "";

					foreach ($tmp as $token)
					{
						if (T_CLASS === $token[0] or T_INTERFACE === $token[0])
						{
							$next = "expecting name";
						}
						elseif (T_STRING === $token[0] and "expecting name" === $next)
						{
							$next = "";
							$modified = filemtime($class_file);
							$class_path = $path . substr($file, 0, - 1 - $ext_len);// relative path + file without extension
							$class_name = $token[1];
							$class_dfn_file = $index_dir . $class_name . "." . $ext;

							// try to read old data for class found
							if (is_readable($class_dfn_file))
							{
								$class_dfn = unserialize(file_get_contents($class_dfn_file));

								if (isset($class_dfn["last_update"]) and $class_dfn["last_update"] === $time)
								{
									throw new awex_clidx_dfn("Duplicate definition of '" . $class_name . "' in '" . $class_dfn["file"] . "' and '" . $class_path . "'.");
								}
							}

							if (
								!isset($class_dfn["last_update"]) or
								false === $modified or
								$class_dfn["last_update"] < $modified or
								$class_dfn["file"] !== $class_path
							)
							{ // previous definition not found or class modified
								// new definition
								$class_dfn = array(
									"file" => $class_path,
									"clidx_version" => 2, // to comply with changes to class index format
									"last_update" => $time
								);

								// update index file
								$cl_handle = @fopen($class_dfn_file, "w");

								if (false === $cl_handle)
								{
									throw new awex_clidx("Unable to update class index for '" . $file . "'.");
								}

								fwrite($cl_handle, serialize($class_dfn));
								fclose($cl_handle);
							}
						}
						elseif ($full_update and T_EXTENDS === $token[0])
						{
							$next = "expecting parent";
						}
						elseif ($full_update and T_STRING === $token[0] and "expecting parent" === $next and !empty($class_dfn) and !empty($class_dfn_file))
						{ // 'extends' always comes right after class name therefore variables are still set.
							$next = "";
							$class_parent = $token[1];
							$class_dfn["extends"] = $class_parent;

							// update index file
							$cl_handle = @fopen($class_dfn_file, "w");

							if (false === $cl_handle)
							{
								throw new awex_clidx_full_upd("Unable to update class index for '" . $file . "'.");
							}

							fwrite($cl_handle, serialize($class_dfn));
							fclose($cl_handle);
						}
					}
				}
				elseif ("dir" === @filetype($class_file) and !in_array($file, $non_dirs))
				{
					self::_update($class_dir . $file . "/", $path . $file . "/", $full_update);
				}
			}

			closedir($handle);
		}
		else
		{
			throw new awex_clidx("Couldn't open class directory.");
		}

		if ($max_execution_time_prev_val !== self::UPDATE_EXEC_TIMELIMIT)
		{
			set_time_limit($max_execution_time_prev_val);
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
		$dir = aw_ini_get("site_basedir");

		// determine if class is aw class or local
		if (0 === strpos($name, self::LOCAL_CLASS_PREFIX))
		{
			// load local class
			$class_file = $dir . self::LOCAL_CLASS_DIR . $name . "." . aw_ini_get("ext");

			if (!is_readable($class_dfn_file))
			{
				throw new awex_clidx("Local class definition not found.");
			}
		}
		else
		{
			// try existing index
			$class_dfn_file = $dir . self::INDEX_DIR . $name . "." . aw_ini_get("ext");
			$class_dir = aw_ini_get("basedir") . self::CLASS_DIR;

			if (!is_readable($class_dfn_file))
			{
				// update index and try again
				self::update();

				if (!is_readable($class_dfn_file))
				{
					throw new awex_clidx("Class definition not found.");
				}
			}

			$class_dfn = unserialize(file_get_contents($class_dfn_file));

			if (1 >= (int) $class_dfn["last_update"])
			{
				self::update();
				$class_dfn = unserialize(file_get_contents($class_dfn_file));
			}

			// load aw class dfn
			$class_file = $class_dir . $class_dfn["file"] . "." . aw_ini_get("ext");

			if (!is_readable($class_file))
			{
				// class file may have changed, update index.
				self::update();

				if (!is_readable($class_dfn_file))
				{
					throw new awex_clidx("Class definition not found.");
				}

				$class_dfn = unserialize(file_get_contents($class_dfn_file));
				$class_file = $class_dir . $class_dfn["file"] . "." . aw_ini_get("ext");

				if (!is_readable($class_file))
				{
					throw new awex_clidx("Class file not found.");
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
			$class_dfn_file = aw_ini_get("site_basedir") . self::INDEX_DIR . $name . "." . aw_ini_get("ext");

			if (!is_readable($class_dfn_file))
			{
				self::update(true); // added with clidx_version 1 -- no check for second update redundancy needed.

				if (!is_readable($class_dfn_file))
				{
					throw new awex_clidx("Class definition not found.");
				}
			}

			$class_dfn = unserialize(file_get_contents($class_dfn_file));

			if (empty($class_dfn["clidx_version"])) // clidx_version must be >=1, earlier formats don't have 'extends' parameter.
			{
				self::update(true);
				$class_dfn = unserialize(file_get_contents($class_dfn_file));
			}

			$parents[] = $class_dfn["extends"];
			$name = isset($class_dfn["extends"]) ? $class_dfn["extends"] : false;
		}
		while ($name or $name === $parent);

		return (bool) in_array($parent, $parents);
	}
}

class awex_clidx extends aw_exception {}
class awex_clidx_full_upd extends awex_clidx {}
class awex_clidx_dfn extends awex_clidx {}

?>
