<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_tree.aw,v 1.2 2005/03/14 17:27:28 kristo Exp $
// property_tree.aw - Puu komponent 
/*

@classinfo syslog_type=ST_PROPERTY_TREE relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property no_caption type=checkbox ch_value=1 
@caption Ilma tekstita

@property demo_content type=textarea rows=6 cols=40
@caption Demo sisu

*/

class property_tree extends class_base
{
	function property_tree()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/property_tree",
			"clid" => CL_PROPERTY_TREE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function get_visualizer_prop($el, &$pd)
	{
		// do the damn tree magic 
		$tv = get_instance(CL_TREEVIEW);

		$tree_opts = array(
			"root_url" => aw_global_get("REQUEST_URI"),	
			"type" => TREE_DHTML,
			"tree_id" => "vist".$el->id(),
			"persist_state" => true,
		);

		$tv->start_tree($tree_opts);

		$ic = get_instance("core/icons");

		$var = "demot_".$el->id()."_";

		$demod = explode("\n", $el->prop("demo_content"));
		foreach($demod as $line)
		{
			if (trim($line) == "")
			{
				continue;
			}
			list($num, $oname) = explode(" ", $line, 2);

			if ($var && $_GET[$var] == $num)
			{
				$oname = "<b>".$oname."</b>";
			}

			$parent = substr($num, 0, strrpos($num, "."));
			if ($parent == "")
			{
				$parent = 0;
			}

			$tv->add_item($parent,array(
				"name" => $oname,
				"id" => $num,
				"url" => aw_url_change_var ($var, $num),
				"iconurl" => ($ic->get_icon_url(CL_FILE,"")),
				"checkbox" => $checkbox_status,
			));
		}

		$pd["type"] = "text";
		$pd["value"] = $tv->finalize_tree();
		
		if ($el->prop("no_caption") == 1)
		{
			$pd["no_caption"] = 1;
		}
	}
}
?>
