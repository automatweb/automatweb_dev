<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/pullout.aw,v 2.11 2003/10/06 14:32:25 kristo Exp $
// pullout.aw - Pullout manager

/*
	@classinfo relationmgr=yes
	
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property groups type=select multiple=1 size=15
	@caption Vali grupid, kellele pullouti näidatakse

	@property docs type=relpicker reltype=RELTYPE_DOCUMENT
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
				
define("RELTYPE_DOCUMENT", 1);

class pullout extends class_base
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
			if (count($meta["groups"]) < 1)
			{
				$found = true;
			}
		}
		else
		{
			$found = true;
		}
		if (count($meta["groups"]) < 1)
		{
			$found = true;
		}

		if (!$found || $meta["docs"] == $oid)
		{
			return "";
		}

		$do = get_instance("document");
		if ($meta["template"] == "")
		{
			if ($GLOBALS["print"] == 1)
			{
				$meta["template"] = "print.tpl";
			}
			else
			{
				$meta["template"] = "plain.tpl";
			}
		}
		$old_print = $GLOBALS["print"];
		$GLOBALS["print"] = 0;
		$_GET["print"] = 0;
		aw_global_set("print", 0);
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
		$GLOBALS["print"] = $old_print;
		$_GET["print"] = $old_print;
		aw_global_set("print", $old_print);
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

        function callback_get_rel_types()
        {
                return array(
                        RELTYPE_DOCUMENT => "n&auml;idatav dokument",
                );
        }
	
}
?>
