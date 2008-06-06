<?php
/*

@classinfo maintainer=dragut

*/
class package_obj extends _int_object
{
	function get_dependencies()
	{
		$ol = new object_list();
		$dep_conns = $this->connections_from(array(
			"class_id" => CL_PACKAGE,
			"type" => "RELTYPE_DEPENDENCY",
		));
		foreach($dep_conns as $conn)
		{
			$ol->add($conn->to());
		}
		return $ol;
	}

	function get_files()
	{
		$ol = new object_list();
		$file_conns = $this->connections_from(array(
			"class_id" => CL_FILE,
			"type" => "RELTYPE_FILE",
		));
		foreach($file_conns as $conn)
		{
			$ol->add($conn->to());
		}
		return $ol;
	}

	function download_package()
	{
		foreach($file_objects->arr() as $file_object)
		{
			$data = $file_object->get_file();
		}
		header("Content-type: application/zip");
		header("Content-length: ".filesize($data["properties"]["file"]));
		header("Content-disposition: inline; filename=".$data["name"].";");
		readfile($data["properties"]["file"]);
		die();
	}

	function get_package_file_names()
	{
		$files = array();
		$file_objects = $this->get_files();
		foreach($file_objects->arr() as $file_object)
		{
			$data = $file_object->get_file();
			$zip = new ZipArchive;
			$zip->open($data["properties"]["file"]);
			for ($i=0; $i<$zip->numFiles;$i++) {
				$dat =  $zip->statIndex($i);
    				if($dat["comp_method"])
				{
					$files[] = $dat["name"];
				}
			}
		}
		return $files;
	}

	function set_package_file_names()
	{
		$files = $this->get_package_file_names();
		$filenamestring = join("<br>\n" , $files);
		$this->set_prop("file_names" , $filenamestring);
		$this->save();
	}

	function get_class_folders($dir)
	{
		$dp = opendir($dir);
		$bd = aw_ini_get("basedir");
		$folders = array();
		while (false !== ($filename = readdir($dp)))
		{
			if($filename != '.' && $filename != '..' && $filename != "CVS")
			{
				$this->fid++;
				if(is_dir($dir.'/'.$filename))
				{
					$folder = array();
					$newdir = $dir.'/'.$filename;
					$folder["id"] = $this->fid;
					$folder["name"] = $filename;
					$folder["folder"] = $newdir;
					$folder["level"] = $this->get_class_folders($newdir);
					$folders[] = $folder;
				}
			}
		}
		usort($folders, array(self, "__file_arr_sort"));
		return $folders;
	}

	function get_contents_class_files($dir)
	{
		$dp = opendir($dir);
		$files = array();
		while (false !== ($filename = readdir($dp)))
		{
			if($filename != '.' && $filename != '..' && substr($filename, 0, 2) != ".#")
			{
				$this->fid++;
				if(!is_dir($dir.'/'.$filename))
				{
					$file = array();
					$file["id"] = $this->fid;
					$file["name"] = $filename;
					$files[] = $file;
				}
			}
		}
		usort($files, array(self, "__file_arr_sort"));
		return $files;
	}

	function __file_arr_sort($a, $b)
	{
		return strcasecmp($a["name"], $b["name"]);
	}


	//this should maybe look for some more files, not just xml props and orbs
	function get_other_files($file)
	{
		$fname = aw_ini_get("basedir").$file;
		$data = @file_get_contents($fname);
		$others = array();
		if($data)
		{
			if(strpos($data, "extends class_base"))
			{
				$name = str_replace(".aw", "", basename($file));
				$xml = array("properties", "orb");
				foreach($xml as $x)
				{
					$check = "/xml/".$x."/".$name.".xml";
					if(file_exists(aw_ini_get("basedir").$check))
					{
						$others[] = $check;
					}
				}
			}
			//this regexp should find the classes template directory, but, well, it doesn't.
			if(ereg('tpldir[\"\']{1}[\s]*\=\>[\s]*[\"\']{1}*([a-zA-Z\_\/0-9]+)[\"\']{1}', $data, $res))
			{
			}
		}
		return $others;
	}
}
?>
