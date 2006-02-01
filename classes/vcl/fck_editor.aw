<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/fck_editor.aw,v 1.1 2006/02/01 14:36:32 ahti Exp $
// fck_editor.aw - FCKeditor

class fck_editor extends aw_template
{
	function rte()
	{
		$this->init(array(
			"tpldir" => "rte",
			"clid" => CL_RTE
		));
	}
	


	function get_rte_toolbar($arr)
	{

		$toolbar = &$arr["toolbar"];
		$toolbar->add_button(array(
			"name" => "source",
			"tooltip" => t("HTML"),
			"target" => "_self",
			"url" => "javascript:oldurl=window.location.href;window.location.href=oldurl + '&no_rte=1';",
		));
		/*
		$js_url_prefix = "";
		if (!empty($arr["target"]))
		{
			$js_url_prefix = "parent.contentarea.";
		};

		$toolbar->add_button(array(
			"name" => "bold",
			"tooltip" => t("Bold"),
			"url" => "javascript:${js_url_prefix}format_selection('bold');",
			"img" => "rte_bold.gif",
		));

		$toolbar->add_button(array(
			"name" => "italic",
			"tooltip" => t("Italic"),
			"url" => "javascript:${js_url_prefix}format_selection('italic');",
			"img" => "rte_italic.gif",
		));

		$toolbar->add_button(array(
			"name" => "underline",
			"tooltip" => t("Underline"),
			"url" => "javascript:${js_url_prefix}format_selection('underline');",
			"img" => "rte_underline.gif",
		));
		$this->get_styles_from_site();
		*/
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
	var fck = new FCKeditor("'.$arr["name"].'");
	fck.BasePath = "js/fckeditor/";
	fck.ToolbarSet = "aw";
	//fck.Width = "700px";
	fck.Height = "500px";
	fck.Config["AutoDetectLanguage"] = false;
	fck.Config["DefaultLanguage"] = "et";
	fck.ReplaceTextarea();
}
-->
</script>
';
		return $retval;
	}
}
?>
