<?php

class class_index
{
	const INDEX_DIR = "/files/class_index/";
	const CLASS_DIR = "/classes/";
	const LOCAL_CLASS_PREFIX = "_aw_local_class__"; // local class names in form OBJ_LOCAL_CLASS_PREFIX . $class_obj_id
	const UPDATE_EXEC_TIMELIMIT = 300;

	public static function update($class_dir = "", $path = "")
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
							// try to read old data for class found
							$class_name = $token[1];
							$class_dfn_file = $index_dir . $class_name . "." . $ext;

							if (is_readable($class_dfn_file))
							{
								$class_dfn = unserialize(file_get_contents($class_dfn_file));
							}

							$modified = filemtime($class_file);
							$class_path = $path . substr($file, 0, - 1 - $ext_len);// relative path + file without extension

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
									"last_update" => $time
								);

								// update index file
								$cl_handle = @fopen($class_dfn_file, "w");

								if (false !== $cl_handle)
								{
									fwrite($cl_handle, serialize($class_dfn));
									fclose($cl_handle);
								}
								else
								{
									throw new awex_clidx("Unable to update class index for '" . $file . "'.");
								}
							}

							$next = "";
						}
					}
				}
				elseif ("dir" === @filetype($class_file) and !in_array($file, $non_dirs))
				{
					self::update($class_dir . $file . "/", $path . $file . "/");
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

	public static function get_file_by_name($name)
	{
		$dir = aw_ini_get("site_basedir");

		// determine if class is aw class or local
		if (0 === strpos($name, self::LOCAL_CLASS_PREFIX))
		{
			// load local class
			$class_file = $dir . "/files/classes/" . $name . "." . aw_ini_get("ext");

			if (!is_readable($class_dfn_file))
			{
				throw new awex_clidx("Local class definition not found.");
			}
		}
		else
		{
			// try existing index
			$class_dfn_file = $dir . "/files/class_index/" . $name . "." . aw_ini_get("ext");
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
}

class awex_clidx extends aw_exception {}

?>