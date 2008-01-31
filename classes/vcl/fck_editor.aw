<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/fck_editor.aw,v 1.19 2008/01/31 13:55:36 kristo Exp $
// fck_editor.aw - FCKeditor
/*
@classinfo  maintainer=hannes
*/
class fck_editor extends aw_template
{
	function fck_editor()
	{
		$this->init (array (
			"tpldir" => "vcl/fck_editor",
		));
	}
	
	function get_rte_toolbar($arr)
	{
		if (!is_object($arr["toolbar"]))
		{
			return;
		}
		$toolbar = &$arr["toolbar"];
		$toolbar->add_separator();
		if($arr["no_rte"] == 1)
		{
			$toolbar->add_button(array(
				"name" => "source",
				"tooltip" => t("RTE"),
				"target" => "_self",
				"url" => aw_url_change_var("no_rte", ""),
			));
		}
		else
		{
			$toolbar->add_button(array(
				"name" => "source",
				"tooltip" => t("HTML"),
				"target" => "_self",
				"url" => "javascript:oldurl=window.location.href;window.location.href=oldurl + '&no_rte=1';",
			));
		}
	}

	function get_styles_from_site($arr = array())
	{
	//	$contents = file_get_contents(aw_ini_get("site_basedir") . "/public/css/styles.css");
		// now I need to parse things out of this place
	//	print "<pre>";
	//	print $contents;
	//	print "</pre>";
	
	}
	
	function draw_editor($arr)
	{
		$this->read_template("fck_editor.tpl");
		$this->submerge=1;
		
		if (isset ($arr["toolbarset"]) )
		{
			$s_toolbarset = $arr["toolbarset"];
		}
		else
		{
			$s_toolbarset = "aw_doc";
		}
		
		$tmp='';
		foreach($arr["props"] as $nm)
        {
			$height = "500px";
			if ($nm == "lead")
			{
				$height = "200px";
			}
			
			// why this?
			//$nm2 = $nm;
			//$nm = str_replace("[","_",$nm);
			//$nm = str_replace("]","_",$nm);
			
			$strFcklang = !empty($arr["lang"]) ? $arr["lang"] : ($_SESSION["user_adm_ui_lc"] != "" ? $_SESSION["user_adm_ui_lc"] : "et");
			if ($strFcklang == "en")
				$strFcklang = "en-uk";
			
			$this->vars(array(
				"name" => $nm,
				"width"=> "600px",
				"height"=> $height,
				"lang" => $strFcklang,
				"toolbarset" => $s_toolbarset,
			));
			$tmp.= $this->parse("EDITOR");
		}
		
		$this->vars(array(
				"EDITOR" => $tmp,
				"msg_leave" => t("Andmed on salvestamata, kas soovite andmed enne lahkumist salvestada?"),
		));
		
		return $this->parse();
	}

	/**
		@attrib name=get_fck_config
	**/
	function get_fck_config($arr)
	{
		die("
			FCKConfig.AutoDetectLanguage	= false ;
			FCKConfig.DefaultLanguage		= '".($_SESSION["user_adm_ui_lc"] != "" ? $_SESSION["user_adm_ui_lc"] : "et")."' ;
		");
	}
}
?>
