<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/rte.aw,v 1.1 2003/10/22 13:42:34 duke Exp $
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

                $toolbar->add_button(array(
                        "name" => "bold",
                        "tooltip" => "Bold",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('bold');",
                        "img" => "rte_bold.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "italic",
                        "tooltip" => "Italic",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('italic');",
                        "img" => "rte_italic.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "underline",
                        "tooltip" => "Underline",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('underline');",
                        "img" => "rte_underline.gif",
                ));

                $toolbar->add_separator();

		$toolbar->add_button(array(
                        "name" => "align_left",
                        "tooltip" => "Align left",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('justifyleft');",
                        "img" => "rte_align_left.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "align_center",
                        "tooltip" => "Align center",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('justifycenter');",
                        "img" => "rte_align_center.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "align_right",
                        "tooltip" => "Align right",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('justifyright');",
                        "img" => "rte_align_right.gif",
                ));

                $toolbar->add_separator();

                $toolbar->add_button(array(
                        "name" => "num_list",
                        "tooltip" => "Numbered list",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('insertorderedlist');",
                        "img" => "rte_num_list.gif",
                ));

		$toolbar->add_button(array(
                        "name" => "bul_list",
                        "tooltip" => "Bulleted list",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('insertunorderedlist');",
                        "img" => "rte_bul_list.gif",
                ));

                $toolbar->add_separator();

                $toolbar->add_button(array(
                        "name" => "outdent",
                        "tooltip" => "Outdent",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('outdent');",
                        "img" => "rte_outdent.gif",
                ));

                $toolbar->add_button(array(
                        "name" => "indent",
                        "tooltip" => "Indent",
                        "target" => "contentarea",
                        "url" => "javascript:format_selection('indent');",
                        "img" => "rte_indent.gif",
                ));

                $toolbar->add_separator();
		$this->read_template("stylebox.tpl");
		$toolbar->add_cdata($this->parse());

		$toolbar->add_cdata("<script>function format_selection(arg){parent.contentarea.format_selection(arg);}</script>");
		$toolbar->add_cdata("<script>function surroundHTML(start,end){parent.contentarea.surroundHTML(start,end);}</script>");

                $toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "clearstyles",
			"tooltip" => "tühista stiilid",
			"target" => "contentarea",
			"url" => "javascript:parent.contentarea.clearstyles()",
                ));
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
