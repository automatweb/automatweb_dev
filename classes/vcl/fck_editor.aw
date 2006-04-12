<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/fck_editor.aw,v 1.6 2006/04/12 14:07:14 kristo Exp $
// fck_editor.aw - FCKeditor

class fck_editor
{
	function get_rte_toolbar($arr)
	{
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
		$retval = '
<script type="text/javascript" src="js/fckeditor/fckeditor.js"></script>
<script type="text/javascript">
<!--
window.onload = function()
{
	';
	foreach($arr["props"] as $nm)
	{
		$w = 600;
		$h = 500;
		if ($nm == "lead")
		{
			$h = 200;
		}
		$nm2 = $nm;
		$nm = str_replace("[","_",$nm);
		$nm = str_replace("]","_",$nm);
		$retval .= '
		var fck'.$nm.' = new FCKeditor("'.$nm2.'");
		fck'.$nm.'.BasePath = "js/fckeditor/";
		fck'.$nm.'.ToolbarSet = "aw";
		fck'.$nm.'.Width = "'.$w.'px";
		fck'.$nm.'.Height = "'.$h.'px";
		fck'.$nm.'.Config["AutoDetectLanguage"] = false;
		fck'.$nm.'.Config["DefaultLanguage"] = "'.(!empty($arr["lang"]) ? $arr["lang"] : "et").'";
		fck'.$nm.'.ReplaceTextarea();';
	}
	$retval .= '
}
-->
</script>
';
		return $retval;
	}
}
?>
