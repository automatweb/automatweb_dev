<?php

/*

@classinfo syslog_type=ST_SWOT relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo strengths caption=Tugevused
@groupinfo weaknesses caption=N&otilde;rkused
@groupinfo opportunities caption=V&otilde;imalused
@groupinfo threats caption=Ohud

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property swot_folder type=relpicker reltype=RELTYPE_FOLDER multiple=1 field=meta method=serialize
@caption SWOT Objektide kataloogid

@property strengths type=text group=strengths field=meta method=serialize
@caption Tugevused

@property weaknesses type=text group=weaknesses field=meta method=serialize
@caption Norkused

@property opportunities type=text group=opportunities field=meta method=serialize
@caption Voimalused

@property threats type=text group=threats field=meta method=serialize
@caption Ohud

*/

define("RELTYPE_FOLDER",1);

class swot extends class_base
{
	function swot()
	{
		$this->init(array(
			'tpldir' => 'workflow/swot/swot',
			'clid' => CL_SWOT
		));
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_FOLDER => "SWOT objektide kataloog"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_FOLDER)
		{
			return array(CL_PSEUDO);
		}
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];

		switch($prop['name'])
		{
			case "strengths":
				$prop['value'] = $this->_mk_table($arr['obj']['oid'], CL_SWOT_STRENGTH);
				break;

			case "weaknesses":
				$prop['value'] = $this->_mk_table($arr['obj']['oid'], CL_SWOT_WEAKNESS);
				break;

			case "opportunities":
				$prop['value'] = $this->_mk_table($arr['obj']['oid'], CL_SWOT_OPPORTUNITY);
				break;

			case "threats":
				$prop['value'] = $this->_mk_table($arr['obj']['oid'], CL_SWOT_THREAT);
				break;
		}
		return PROP_OK;
	}

	function _mk_table($oid, $clid)
	{
		$ob = $this->get_object($oid);

		$sobjs = array();

		$arr = new aw_array($ob['meta']['swot_folder']);
		foreach($arr->get() as $parent)
		{
			$sobjs += $this->get_objects_below(array(
				"parent" => $parent,
				"class" => $clid,
				"full" => true,
				"ret" => ARR_ALL
			));
		}

		$tb = new aw_table(array("layout" => "generic"));
		$tb->define_field(array(
			"caption" => "Jrk",
			"name" => "jrk",
			"sortable" => 1
		));

		$tb->define_field(array(
			"caption" => "Nimi",
			"name" => "name",
			"sortable" => 1
		));

		$tb->define_field(array(
			"caption" => "Sisu",
			"name" => "comment",
			"sortable" => 1
		));
		foreach($sobjs as $s_oid => $s_row)
		{
			$s_row["name"] = html::href(array(
				'url' => $this->mk_my_orb("change", array("id" => $s_row["oid"]),$this->cfg["classes"][$clid]["file"]),
				'caption' => $s_row['name']
			));
			$tb->define_data($s_row);
		}
		$tb->set_default_sortby("jrk");
		$tb->sort_by();
		return $tb->draw();
	}
}
?>
