<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/rte.aw,v 1.6 2004/04/22 12:17:03 duke Exp $
// rte.aw - Rich Text Editor 
/*

@classinfo syslog_type=ST_RTE relationmgr=yes

@default table=objects
@default group=general

*/

class rte extends class_base
{
	function rte()
	{
		$this->init(array(
			"tpldir" => "rte",
			"clid" => CL_RTE
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	

	*/

	function get_rte_toolbar($arr)
	{
		$toolbar = &$arr["toolbar"];
		$toolbar->add_separator();

		$js_url_prefix = "";
		if (!empty($arr["target"]))
		{
			$js_url_prefix = "parent.contentarea.";
		};

                $toolbar->add_button(array(
                        "name" => "bold",
                        "tooltip" => "Bold",
                        "url" => "javascript:${js_url_prefix}format_selection('bold');",
                        "img" => "rte_bold.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "italic",
                        "tooltip" => "Italic",
                        "url" => "javascript:${js_url_prefix}format_selection('italic');",
                        "img" => "rte_italic.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "underline",
                        "tooltip" => "Underline",
                        "url" => "javascript:${js_url_prefix}format_selection('underline');",
                        "img" => "rte_underline.gif",
                ));

                $toolbar->add_separator();

		$toolbar->add_button(array(
                        "name" => "align_left",
                        "tooltip" => "Align left",
                        "url" => "javascript:${js_url_prefix}format_selection('justifyleft');",
                        "img" => "rte_align_left.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "align_center",
                        "tooltip" => "Align center",
                        "url" => "javascript:${js_url_prefix}format_selection('justifycenter');",
                        "img" => "rte_align_center.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "align_right",
                        "tooltip" => "Align right",
                        "url" => "javascript:${js_url_prefix}format_selection('justifyright');",
                        "img" => "rte_align_right.gif",
                ));

                $toolbar->add_separator();

                $toolbar->add_button(array(
                        "name" => "num_list",
                        "tooltip" => "Numbered list",
                        "url" => "javascript:${js_url_prefix}format_selection('insertorderedlist');",
                        "img" => "rte_num_list.gif",
                ));

		$toolbar->add_button(array(
                        "name" => "bul_list",
                        "tooltip" => "Bulleted list",
                        "url" => "javascript:${js_url_prefix}format_selection('insertunorderedlist');",
                        "img" => "rte_bul_list.gif",
                ));

                $toolbar->add_separator();

                $toolbar->add_button(array(
                        "name" => "outdent",
                        "tooltip" => "Outdent",
                        "url" => "javascript:${js_url_prefix}format_selection('outdent');",
                        "img" => "rte_outdent.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "indent",
                        "tooltip" => "Indent",
                        "url" => "javascript:${js_url_prefix}format_selection('indent');",
                        "img" => "rte_indent.gif",
                ));

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "source",
			"tooltip" => "HTML",
			"target" => "_self",
			"url" => "javascript:oldurl=window.location.href;window.location.href=oldurl + '&no_rte=1';",
		));

               
	       	/*
                $toolbar->add_separator();
		$this->read_template("stylebox.tpl");
		$toolbar->add_cdata($this->parse(),"right");
		*/

                $toolbar->add_separator(array(
			"side" => "right",
		));

		$toolbar->add_button(array(
			"name" => "clearstyles",
			"img" => "clearstyles.gif",
			"tooltip" => "t�hista stiilid",
			"url" => "javascript:${js_url_prefix}clearstyles()",
			"side" => "right",
                ));
	}

	function draw_editor($arr)
	{
                // richtext editors are inside a template
                static $rtcounter = 0;
                $rtcounter++;
                $retval = "";
                $this->read_template("aw_richtexteditor.tpl");
                $this->vars($arr);

                if ($rtcounter == 1)
                {
                        $this->rt_elements = array($arr["name"]);
                        // get the site styles
                        $site_styles = $this->get_file(array(
                                "file" => aw_ini_get("site_basedir") . "/public/css/styles.css",
                        ));
                        preg_match("/(\.text \{.+?\})/sm",$site_styles,$m);
                        $text_style = str_replace("\n"," ",$m[1]);
			$text_style = str_replace("\r","",$text_style);

                        $this->vars(array(
                                "rte_styles" => $text_style,
                                //"rte_styles" => $text_style . " .styl1 {color: green; font-family: Verdana; font-weight: bold;} .styl2 {color: blue; font-size: 20px;} .styl3 {color: red; border: 1px solid blue;}",
                        ));
                        $retval .= $this->parse("writer");
                        $retval .= $this->parse("toolbar");
                };
                $retval .= $this->parse("field");
                return $retval;
        }
};
?>
