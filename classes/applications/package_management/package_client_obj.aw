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
//
//			$handle = fopen($url, "r");//arr($handle);
//			$contents = fread($handle, $fs);arr($contents);
//$contents = $this->curl_get_file_contents($url);arr($contents);
			$contents = file_get_contents($url);
			$fn = aw_ini_get("server.tmpdir")."/".gen_uniq_id().".zip";
			$fp = fopen($fn, 'w');
			fwrite($fp, $contents);
			fclose($fp);

			$zip = new ZipArchive;
			$zip->open($fn);
//			arr($zip->numFiles);
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
					$path = substr($path , strpos($path,"/", 1)+1);
					print aw_ini_get("basedir").'/'.$path ." ... ";
	//selle peab paremini t88le saama... p2rast ei jaksa keegi seda jama kustutada muidu
	//				$res = $zip->extractTo(aw_ini_get("basedir") , array($dat["index"]));
					print ($res? "6nnestus" : "ei 6nnestunud")." <br>\n";	
					print aw_ini_get("basedir").$dat["name"]." <br>\n";	
				}
			}
		}
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
