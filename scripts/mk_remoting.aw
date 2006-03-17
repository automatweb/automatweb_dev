<?php
$basedir = realpath(".");
include("$basedir/init.aw");
init_config(array("ini_files" => array("$basedir/aw.ini")));
classload("defs");
classload("aw_template");
aw_global_set("no_db_connection", true);

$anal = get_instance("core/aw_code_analyzer/aw_code_analyzer");

$fn_c = <<<EOD
	function %s(\$args)
	{
		//\$args = func_get_args();
		return \$this->do_orb_method_call(array(
			"class" => "%s",
			"action" => "%s",
			"method" => "xmlrpc",
			"server" => \$this->remote_server,
			"params" => \$args
		));
	}
EOD;

$construct = <<<CONSTRUCT
	function %s(\$rs)
	{
		\$this->init();
		\$this->remote_server = \$rs;
	}
CONSTRUCT;

$orb = get_instance("core/orb/orb");

$clss = aw_ini_get("classes");
foreach($clss as $clid => $cld)
{
	if (($rs = $cld["is_remoted"]) != "")
	{
		echo "generating proxy for class $cld[file] \n";
		// make proxy class
		$proxy_path = aw_ini_get("basedir")."/classes/core/proxy_classes/".basename($cld["file"]).".aw";
		$proxy_class = "__aw_proxy_".basename($cld["file"]);

		$real_path = aw_ini_get("basedir")."/classes/".$cld["file"].".aw";
		$real_class = basename($cld["file"]);

		$prx = array();
		$prx[] = sprintf($construct, $proxy_class);

		$orb_data = $orb->load_xml_orb_def($real_class);

		$file_data = $anal->analyze_file($real_path, true);
		$class_data = $file_data["classes"][$real_class];
		foreach($class_data["functions"] as $fnm => $fd)
		{
			if ($fnm == $real_class)
			{
				continue;
			}

			// here the function name must be the class functionm name
			// but the action called must be the orb action name for that class
			$orb_action_name = $fnm;
			foreach($orb_data[$real_class] as $orb_action => $act_data)
			{
				if ($act_data["function"] == $fnm)
				{
					$orb_action_name = $orb_action;
				}
			}

			$prx[] = sprintf($fn_c, $fnm, $real_class, $orb_action_name);
		}
		
		$prxstr = "<?php\n\nclass $proxy_class extends core\n{\n".join("\n\n", $prx)."\n}\n\n?>";

		$fp = fopen($proxy_path, "w");
		fwrite($fp, $prxstr);
		fclose($fp);
	}
}
?>
