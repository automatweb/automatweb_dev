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

	function get_sites_used()
	{
		$ret = array();
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_SITE_RELATION",
		));
		foreach($conns as $conn)
		{
			$o = $conn->to();
			$ret[] = $o->prop("site");
		}
		return $ret;
	}

	function download_package()
	{
		$file_objects = $this->get_files();
		foreach($file_objects->arr() as $file_object)
		{
			$data = $file_object->get_file();
		}

		
/*		header ("Content-Type: application/xml");

		$out_charset = "ISO-8859-4";
		$this_object = obj ($arr["id"]);
		$import_url = $this_object->prop ("city24_import_url");
		$xml = file_get_contents ($import_url);
		// $xml = iconv ("UTF-8", $out_charset, $xml);
		// $xml = preg_replace ('/encoding\=\"UTF\-8\"/Ui', 'encoding="' . $out_charset . '"', $xml, 1);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $import_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
		// $xml = curl_exec($ch);
		curl_exec($ch);
		curl_close($ch);

		// echo $xml;
		exit;
*/

		header("Content-type: application/zip");
		header("Content-length: ".filesize($data["properties"]["file"]));
		header("Content-disposition: inline; filename=".$data["name"].";");
		print $data["content"];
		flush();
		die();
	}

	function get_package_file_size()
	{
		$file_objects = $this->get_files();
		foreach($file_objects->arr() as $file_object)
		{
			$data = $file_object->get_file();
		}
		return filesize($data["properties"]["file"]);
	}

	function add_site($site_id)
	{
		$rel = new object();
		$rel->set_class_id(CL_PACKAGE_SITE_RELATION);
		$rel->set_parent($this->id());
		$rel->set_prop("site" , $site_id);
		$rel->save();
		$this->connect(array(
			"to" => $rel->id(),
			"reltype" => "RELTYPE_SITE_RELATION"
		));
		return $rel->id();
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
