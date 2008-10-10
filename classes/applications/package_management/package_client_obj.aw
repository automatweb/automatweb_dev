<?php

class package_client_obj extends _int_object
{
	function get_packages($filter)
	{
		$inst = $this->instance();
		$packages = array();
		if($this->prop("packages_server"))
		{
			$packages = $inst->do_orb_method_call(array(
				"class" => "package_server",
				"action" => "download_package_list",
				"method" => "xmlrpc",
				"server" => $this->prop("packages_server"),
				"no_errors" => true,
				"params" => $filter,
			));
		}
		return $packages;
	}

	function get_my_packages()
	{
		$filter = array("site_id" => $this->site_id());
		$inst = $this->instance();
		$packages = array();
		if($this->prop("packages_server"))
		{
			$packages = $inst->do_orb_method_call(array(
				"class" => "package_server",
				"action" => "download_package_list",
				"method" => "xmlrpc",
				"server" => $this->prop("packages_server"),
				"no_errors" => true,
				"params" => $filter,
			));
		}
		return $packages;
	}

	function get_made_packages()
	{
		$filter = array(
			'class_id' => CL_PACKAGE,
			'parent' => $this->prop('packages_folder_aw'),
			'site_id' => array(),
			'lang_id' => array(),
		);

		$ol = new object_list($filter);

		$server_packages = $this->get_packages();
//see j22b aeglaseks varsti, a kyll siis ymber teeb
		foreach($ol->arr() as $o)
		{
			foreach($server_packages as $p)
			{
				if($o->name() == $p["name"] && $o->prop("version") == $p["version"])
				{
			//		$ol->remove($o->id());
					continue;
				}
			}
		}
		
		return $ol;
	}

	function download_package($id)
	{
		if($this->prop("packages_server"))
		{
			$url = $this->prop("packages_server")."/orb.aw?class=package_server&action=download_package_file&id=".$id."&site_id=".$this->site_id();
			$inst = $this->instance();

			$fs = $inst->do_orb_method_call(array(
				"class" => "package_server",
				"action" => "get_package_file_size",
				"method" => "xmlrpc",
				"server" => $this->prop("packages_server"),
				"no_errors" => true,
				"params" => array(
					'id' => $id,
				),
			));

			$data = $this->do_orb_method_call(array(
				"class" => "package_server",
				"action" => "download_package_properties",
				"method" => "xmlrpc",
				"server" => $this->prop("packages_server"),
				"no_errors" => true,
				"params" => array(
					'id' => $id,
				),
			));	
//
//			$handle = fopen($url, "r");//arr($handle);
//			$contents = fread($handle, $fs);arr($contents);
//$contents = $this->curl_get_file_contents($url);arr($contents);
			$contents = file_get_contents($url);
			$fn = aw_ini_get("server.tmpdir")."/".gen_uniq_id().".zip";
			$fp = fopen($fn, 'w');
			fwrite($fp, $contents);
			fclose($fp);

			$this->install_package($data + array("file_name" => $fn));

			$this->add_package(array(
				"name" => $data["name"],
				"version" => $data["version"],
				"description" => $data["description"],
				"file" => $fn,
			));
		}
	}

	private function install_package($data)
	{
		$zip = new ZipArchive;
		$zip->open($data["file_name"]);
//		arr($zip->numFiles);
		if ($this->db_table_exists("site_file_index") === false)
		{
			$this->db_query('create table site_file_index (
				id int not null primary key auto_increment,
				file_name varchar(255),
				file_version varchar(31),
				file_location varchar(31),
				package_name varchar(255),
				package_version varchar(31),
				used int,
				installed_date int,
				dependences varchar(255),
			)');//see viimane on niisama, 2kki leiab hea lahenduse selleks
		}

		for ($i=0; $i<$zip->numFiles;$i++) {
			$dat =  $zip->statIndex($i);
			if($dat["comp_method"])
			{
				if($dat["index"] == 2) $dir = $dat["name"];
				$files[] = $dat["name"];//arr($dat);
				$path = substr($dat["name"] , strpos($dat["name"],"/", 1)+1);
				if(!strpos($path,"/", 1))
				{
					continue;
				}

				$sql = "insert into ".$this->db_table_name."(
					file_name,
					file_version,
					file_location,
					package_name,
					package_version,
					used,
					installed_date,
					dependences,
				) values (
					".$dat["name"].",
					"."123".",
					'"."123"."',
					'".$data["name"]."',
					'".$data["version"]."',
					'1',
					'".time()."',
					'"."123"."',
				)";
arr($sql);	
//				$this->db_query($sql);

				$path = substr($path , strpos($path,"/", 1)+1);
				print aw_ini_get("basedir").'/'.$path ." ... ";
	//selle peab paremini t88le saama... p2rast ei jaksa keegi seda jama kustutada muidu
	//			$res = $zip->extractTo(aw_ini_get("basedir") , array($dat["index"]));
				print ($res? "6nnestus" : "ei 6nnestunud")." <br>\n";	
				print aw_ini_get("basedir").$dat["name"]." <br>\n";	
			}
		}
	}

	function add_package($params)
	{
		$o = new object();
		$o->set_class_id(CL_PACKAGE);
		$o->set_parent($this->id());
		$o->set_name($params["name"] ? $params["name"] : t("Nimetu pakett"));

		$o->set_prop("version" , $params["version"]);
		$o->set_prop("description" , $data["description"]);
		$o->set_prop("installed" , 1);
		$o->save();

		$file = new object();
		$file->set_class_id(CL_FILE);
		$file->set_parent($o->id());
		$file->set_name($o->name());
		$file->save();

		$o->connect(array(
			"to" => $file->id(),
			"reltype" => "RELTYPE_FILE",
		));

		if(file_exists($params["file"]))
		{
			$handle = fopen($params["file"], "r");
			$contents = fread($handle, filesize($params["file"]));
			$type = "zip";
		
			fclose($handle);
		
			$data["id"] = $file->id();
			$data["return"] = "id";
			$data["file"] = array(
				"content" => $contents,
				"name" => $o->name(),
				"type" => $type,
			);
			$t = get_instance(CL_FILE);
			$rv = $t->submit($data);
		}
		return $o->id();
	}


	function upload_package($id)
	{
		$client = $this->instance();
		$url = $this->prop("packages_server")."/orb.aw?class=package_server&action=upload_package&id=".$id."&site_id=".$this->site_id()."&return_url=".urlencode($client->mk_my_orb("change", array(
					"id" => $this->id(),
					"clid" => CL_PACKAGE_CLIENT,
					"group" => "packages",
				)));
		header("Location: ".$url);
		die();
		$this->do_nothing();
	}

	function do_nothing()
	{
		sleep(5);
		return ;
	}

	function curl_get_file_contents($URL)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);
	
		if ($contents) return $contents;
		else return FALSE;
	}

	function get_files_list($id)
	{
		$files = array();
		if($this->prop("packages_server"))
		{
			$inst = $this->instance();
			$files = $inst->do_orb_method_call(array(
				"class" => "package_server",
				"action" => "download_package_files",
				"method" => "xmlrpc",
				"server" => $this->prop("packages_server"),
				"no_errors" => true,
				"params" => array(
					'id' => $id,
				),
			));
		}
		return $files;
	}
}

?>
