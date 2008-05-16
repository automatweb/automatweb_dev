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
}
?>
