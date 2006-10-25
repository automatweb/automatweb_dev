<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/fck_editor.aw,v 1.11 2006/10/25 10:37:38 kristo Exp $
// fck_editor.aw - FCKeditor

class fck_editor extends core
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
function FCKeditor_OnComplete( editorInstance )
{
	var browser = navigator.userAgent.toLowerCase();
	var int_content_length_original = editorInstance.EditorDocument.body.innerHTML.length;
	var bool_changed=false;
	
	if (browser.indexOf(\'msie\')>0)	{
		editorInstance.Events.AttachEvent( \'OnSelectionChange\', FCKeditor_OnChange ) ;
		editorInstance.EditorDocument.attachEvent( \'onkeyup\', FCKeditor_OnChange ) ;
		editorInstance.EditorDocument.attachEvent( \'onkeydown\', FCKeditor_OnChange ) ;
	} 
	else 
	{
		editorInstance.Events.AttachEvent( \'OnSelectionChange\', FCKeditor_OnChange ) ;
		editorInstance.EditorDocument.addEventListener( \'keyup\', FCKeditor_OnChange, true ) ;
		editorInstance.EditorDocument.addEventListener( \'keydown\', FCKeditor_OnChange, true ) ;
	}
	
	function FCKeditor_OnChange(  )
	{
		if (int_content_length_original!=editorInstance.EditorDocument.body.innerHTML.length && bool_changed == false)
		{
			bool_changed = true;
			set_changed();
		}
	}
}
oldload = window.onload;
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
		fck'.$nm.'.Config["DefaultLanguage"] = "'.(!empty($arr["lang"]) ? $arr["lang"] : ($_SESSION["user_adm_ui_lc"] != "" ? $_SESSION["user_adm_ui_lc"] : "et")).'";
		fck'.$nm.'.ReplaceTextarea();';

		$retval .= 'fck'.$nm.'.Config["CustomConfigurationsPath"] = "'.$this->mk_my_orb("get_fck_config").'" + ( new Date() * 1 ) ;'."\n";
	}
	$retval .= '
 	if (oldload)
	{
		oldload();
	}
}
-->
</script>
';
		return $retval;
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
