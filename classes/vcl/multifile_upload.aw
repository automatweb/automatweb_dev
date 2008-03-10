<?php
/*
@classinfo  maintainer=hannes
*/
class multifile_upload extends class_base
{
	function multifile_upload()
	{
		$this->init(array(
			"tpldir" => "vcl/multifile_upload",
		));
	}

	function init_vcl_property($arr)
	{
		$this->read_template("multifile_upload.tpl");
		$content = "";
		$tmp = "";	
		
		// read props from the given class
		$prop = $arr["prop"];
	
		$tp = $arr["prop"];
		$tp["type"] = "text";
		
		
		if ($arr["new"] != 1)
		{
			$i = 1;
			foreach($arr["obj_inst"]->connections_from(array("type" => $arr["prop"]["reltype"])) as $file)
			{
				$fo =  $file->to();
				$file_instance = $fo->instance();
				
				$this->vars(array(
					"id" => $fo->id(),
					"counter" => $i++,
					"file_name"=>$fo -> name(),
					"file_url" => $file_instance->get_url($fo->id(), $fo->name()),
					"edit_url" => html::get_change_url($fo->id()),
					"delete_url" => aw_ini_get("baseurl")."/automatweb/orb?class=multifile_upload&action=ajax_delete_obj&id=".$fo->id(),
				));
				$tmp .= $this->parse('file');
			}
		}
		
		
		$this->vars(array(
			"file" => $tmp
		));
		
		
		$content = $this->parse();
		
		$tp["value"] = $content;
		return array($tp["name"] => $tp);
	}

	function process_vcl_property($arr)
	{

	}

	function callback_post_save($arr)
	{
		$parent = $arr["obj_inst"]->parent();
		$oid = $arr["obj_inst"]->id();
		$clid = $arr["obj_inst"]->class_id();
		$o = obj($oid);
		if($arr["prop"]["image"])
		{
			$fi = get_instance(CL_IMAGE);
		}
		else
		{
			$fi = get_instance(CL_FILE);
		}
		$files = $fi -> add_upload_multifile("file", $parent);
		foreach ($files as $file)
		{
			$o->connect(array(
				"to" => $file["id"],
				"type" => $arr["prop"]["reltype"],
			));
		}
	}

	/**
	@attrib name=ajax_delete_obj
	
	@param id required type=int
	
	@comment
		Get directory listing
	**/
	function ajax_delete_obj ($arr)
	{
		$o = obj($arr["id"]);
		$o -> delete();
		die();
	}
}
?>
