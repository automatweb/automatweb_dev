<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/pullout.aw,v 2.6 2002/12/17 18:26:41 duke Exp $
// pullout.aw - Pullout manager

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property groups type=select multiple=1 size=15
	@caption Vali grupid, kellele pullouti näidatakse

	@property docs type=objpicker clid=CL_DOCUMENT
	@caption Vali dokument, mida näidata

	@property align type=select
	@caption Align

	@property right type=textbox size=10
	@caption Paremalt

	@property width type=textbox size=10
	@caption Laius

	@property template type=select
	@caption Template
*/
				
class pullout extends aw_template
{
	function pullout()
	{
		$this->init(array(
			"tpldir" => "pullout",
			"clid" => CL_PULLOUT,
		));
		$this->align = array(
			"left" => "Vasak",
			"center" => "Keskel",
			"right" => "Paremal"
		);
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "align":
				$data["options"] = $this->align;
				break;

			case "groups":
				$u = get_instance("users");
				$data["options"] = $u->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));
				break;

			case "template":
				$data["options"] = $this->get_template_picker();
				break;

		}
		return PROP_OK;
	}

	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
    if (!$alias)
    {
			return "";
    }
		return $this->view(array("id" => $alias["target"],"doc" => $oid));
	}

	function view($arr)
	{
		extract($arr);
		$o = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$gidlist = aw_global_get("gidlist");
		$found = false;
		if (is_array($meta["groups"]))
		{
			foreach($meta["groups"] as $gid)
			{
				if ($gidlist[$gid] == $gid)
				{
					$found = true;
				}
			}
		}

		if (!$found || $meta["docs"] == $oid)
		{
			return "";
		}

		$do = get_instance("document");
		$this->read_template($meta["template"]);
		$this->vars(array(
			"width" => $meta["width"],
			"align" => $meta["align"],
			"right" => $meta["right"],
			"content" => $do->gen_preview(array(
				"docid" => $meta["docs"]
			)),
			"title" => $o["name"]
		));
		return $this->parse();
	}

	function get_template_picker()
	{
		$ret = array();
		$this->db_query("SELECT name,filename FROM template WHERE type = 3");
		while ($row = $this->db_next())
		{
			$ret[$row["filename"]] = $row["name"];
		}
		return $ret;
	}
}
?>
