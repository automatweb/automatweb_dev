<?php
// $Id: treeview.aw,v 1.1 2002/11/04 16:05:06 duke Exp $
// treeview.aw - tree generator
/*
        @default table=objects
        @default group=general
        @property root type=select field=meta method=serialize
        @caption Root objekt
        
	@property treetype type=select field=meta method=serialize
        @caption Puu tüüp

*/
class treeview extends aw_template
{
	function treeview($args = array())
	{
		$this->init("treeview");
	}

	function add($args = array())
	{
		extract($args);
		// gotta get rid of that too
		$cfg = get_instance("cfg/cfgmanager");
		return $cfg->change(array(
			"class_id" => CL_TREEVIEW,
			"parent" => $parent,
		));
	}

	function change($args = array())
	{
		$cfg = get_instance("cfg/cfgmanager");
		return $cfg->change(array(
			"id" => $args["id"],
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "root":
				$ob = get_instance("objects");
                                $data["options"] = $ob->get_list();
				break;

			case "treetype":
				$data["options"] = array("" => "--vali--","dhtml" => "DHTML (Ftiens)");
				break;
		};
	}

	function show($args = array())
	{
		// generates the tree
		extract($args);
		$obj = $this->get_object($id);
		$root = $obj["meta"]["root"];
		$rootobj = $this->get_object($root);
		if (!$rootobj)	
		{
			return "invalid root object";
		};
		$type = $obj["meta"]["treetype"];
		if (!$type)
		{
			$type = "dhtml";
		};
		$this->read_template("ftiens.tpl");
		$arr = array();
                $mpr = array();
                $this->listacl("objects.status != 0 AND objects.class_id = ".CL_PSEUDO);
                // listib koik menyyd ja paigutab need arraysse	
		$mn = get_instance("menuedit");
                $mn->db_listall("objects.status != 0 AND menu.type != ".MN_FORM_ELEMENT,true);
                while ($row = $mn->db_next())
                {
                        if ($this->can("view",$row["oid"]))
                        {
                                $row["name"] = str_replace("\"","&quot;", $row["name"]);
                                $arr[$row["parent"]][] = $row;
                                $mpr[] = $row["parent"];
                        }
                }
                // objektipuu
                $tr = $this->rec_tree(&$arr, $root,$period);
		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $root,
			"rootname" => $rootobj["name"],
                ));

		return $this->parse();
	}
	
	function rec_tree(&$arr,$parent,$period)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			if (!isset($row["mtype"]) || $row["mtype"] != MN_HOME_FOLDER)
			{
				// tshekime et kas menyyl on submenyysid
				// kui on, siis n2itame alati
				// kui pole, siis tshekime et kas n2idatakse perioodilisi dokke
				// kui n2idatakse ja menyy on perioodiline, siis n2itame menyyd
				// kui pole perioodiline siis ei n2ita
				$sub = $this->rec_tree(&$arr,$row["oid"],$period);
				$iconurl = $row["icon_id"] > 0 ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif";
				$url = ($row["link"]) ? $row["link"] : $this->cfg["baseurl"] . "/" . $row["oid"];
				$this->vars(array(
					"name" => $row["name"],
					"id" => $row["oid"],
					"parent" => $row["parent"],
					"iconurl" => $iconurl,
					"url" => $url,
					"targetframe" => "right",
				));
				if ($sub == "")
				{
					$ret.=$this->parse("DOC");
				}
				else
				{
					$ret.=$this->parse("TREE").$sub;
				}
			}
		}
		return $ret;
	}

};
?>
