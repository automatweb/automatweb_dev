<?php
// $Header: /home/cvs/automatweb_dev/classes/workflow/swot/Attic/swot.aw,v 1.8 2004/10/27 12:04:27 kristo Exp $
/*

@classinfo syslog_type=ST_SWOT relationmgr=yes no_status=1

@groupinfo strengths caption=Tugevused
@groupinfo weaknesses caption=Nõrkused
@groupinfo opportunities caption=Võimalused
@groupinfo threats caption=Ohud
@groupinfo view caption=Üldvaade

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property swot_folder type=relpicker reltype=RELTYPE_SWOT_FOLDER multiple=1 
@caption SWOT Objektide kataloogid

@property strengths type=text group=strengths no_caption=1
@caption Tugevused

@property weaknesses type=text group=weaknesses no_caption=1
@caption Nõrkused

@property opportunities type=text group=opportunities no_caption=1
@caption Võimalused

@property threats type=text group=threats no_caption=1
@caption Ohud

@property view type=text group=view no_caption=1

@reltype SWOT_FOLDER value=1 clid=CL_MENU
@caption SWOT objektide kataloog

*/


class swot extends class_base
{
	function swot()
	{
		$this->init(array(
			'tpldir' => 'workflow/swot/swot',
			'clid' => CL_SWOT
		));
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];

		switch($prop['name'])
		{
			case "strengths":
				$prop['value'] = $this->_mk_table($arr['obj_inst']->id(), CL_SWOT_STRENGTH);
				break;

			case "weaknesses":
				$prop['value'] = $this->_mk_table($arr['obj_inst']->id(), CL_SWOT_WEAKNESS);
				break;

			case "opportunities":
				$prop['value'] = $this->_mk_table($arr['obj_inst']->id(), CL_SWOT_OPPORTUNITY);
				break;

			case "threats":
				$prop['value'] = $this->_mk_table($arr['obj_inst']->id(), CL_SWOT_THREAT);
				break;

			case "view":
				$prop['value'] = $this->show(array("oid" => $arr['obj_inst']->id()));
				break;
		}
		return PROP_OK;
	}

	function _mk_table($oid, $clid)
	{
		$ob = new object($oid);

		$arr = new aw_array($ob->prop('swot_folder'));

		$sobjs = new object_list(array(
                        "class_id" => $clid,
			// is this right?
                        "parent" => $ob->prop("swot_folder"),
                ));


		$tb = new aw_table(array("layout" => "generic",'prefix' => "sw_".$clid));

		$clss = aw_ini_get("classes");
		$tb->define_field(array(
			"caption" => $clss[$clid]["name"],
			"name" => "name",
			"sortable" => 1
		));

		$tb->define_field(array(
			"caption" => "Klassifikaatorid",
			"name" => "clf",
			"sortable" => 1
		));

		$tb->define_field(array(
			"caption" => "Sisu",
			"name" => "comment",
			"sortable" => 1
		));
                foreach($sobjs->arr() as $sobj)
		{
			$s_row = array();
			$s_row["name"] = html::href(array(
				'url' => $this->mk_my_orb("change", array("id" => $sobj->id()),$clss[$clid]["file"]),
				'caption' => $sobj->name(),
			));

			$clf_obj = new object($sobj->prop("clf"));
			$s_row["clf"] = $clf_obj->name();

			$tb->define_data($s_row);
		}
		$tb->set_default_sortby("jrk");
		$tb->sort_by();
		return $tb->draw();
	}

	function show($arr)
	{
		extract($arr);
		$this->read_template("show.tpl");

		$this->vars(array(
			"strengths" => $this->_mk_table($oid, CL_SWOT_STRENGTH),
			"weaknesses" => $this->_mk_table($oid, CL_SWOT_WEAKNESS),
			"threats" => $this->_mk_table($oid, CL_SWOT_THREAT),
			"opportunities" => $this->_mk_table($oid, CL_SWOT_OPPORTUNITY),
		));
		return $this->parse();
	}
}
?>
