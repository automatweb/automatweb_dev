<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/classificator.aw,v 1.4 2004/02/26 13:56:39 duke Exp $

/*

@classinfo syslog_type=ST_CLASSIFICATOR relationmgr=yes


@default table=objects
@default group=general

@property comment type=textarea cols=50 rows=5 field=comment
@caption Kommentaar

@default field=meta
@default method=serialize

@property folders type=relpicker reltype=RELTYPE_FOLDER multiple=1
@caption Kus kehtib

@property clids type=select multiple=1 
@caption Klassid millele kehtib

@reltype FOLDER value=1 clid=CL_MENU
@caption hallatav kataloog

*/

class classificator extends class_base
{
	function classificator()
	{
		$this->init(array(
			'clid' => CL_CLASSIFICATOR
		));
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		if ($prop['name'] == "clids")
		{
			$prop['options'] = aliasmgr::get_clid_picker();
		}

		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		extract($arr);
		$ob = new object($id);
		$this->db_query("DELETE FROM classificator2menu WHERE clf_id = '".$id."'");
		$arr = $ob->prop("folders");
		foreach($arr->get() as $_fid => $_tt)
		{
			$_arr = new aw_array($ob->prop('clids'));
			foreach($_arr->get() as $clid)
			{
				// so how do I use storage for queries like this? -- duke
				$this->db_query("INSERT INTO classificator2menu(menu_id, class_id, clf_id) VALUES('".$_tt."','".$clid."','".$id."')");
			}
		}
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		$ot = get_instance(CL_OBJECT_TYPE);
		$ff = $ot->get_obj_for_class(array(
			"clid" => $arr["clid"],
		));
		$oft = new object($ff);
		$meta = $oft->meta("classificator");
		$ofto = new object($meta[$prop["name"]]);
		$olx = new object_list(array(
			"parent" => $ofto->id(),
			"class_id" => CL_META,
			"lang_id" => array(),
		));
		$prop["type"] = "select";
		$prop["options"] = array("" => "") + $olx->names();
		$prop["caption"] = $ofto->name();
	}

	////
	// !returns a list of id => name of classificators for specified folder/clid combo
	// parameters:
	//	clid - class id 
	//	parent - folder
	function get_clfs($arr)
	{
		extract($arr);
		if ($add_empty)
		{
			$ret = array("0" => "");
		}
		else
		{
			$ret = array();
		}

		$ch = $this->get_object_chain($parent);
		foreach($ch as $id => $name)
		{
			$found = false;
			$this->db_query("
				SELECT 
					o.name as name,o.oid as oid 
				FROM 
					classificator2menu c
					LEFT JOIN objects o ON o.oid = c.clf_id
				WHERE 
					c.class_id = '$clid' AND 
					c.menu_id = '$id'
			");
			while($row = $this->db_next())
			{
				$found = true;
	 			$ret[$row["oid"]] = $row["name"];
			}

			if ($found)
			{
				return $ret;
			}
		}

		return $ret;
	}
}
?>
