<?php
// $Id: treeview.aw,v 1.3 2002/11/07 10:52:37 kristo Exp $
// treeview.aw - tree generator
/*
        @default table=objects
        @default group=general
        @property root type=select field=meta method=serialize
        @caption Root objekt

	@property rootcaption type=textbox field=meta method=serialize
	@caption Root objekti nimi
	
	@property icon_root type=objpicker clid=CL_ICON field=meta method=serialize
	@caption Root objekti ikoon
        
	@property treetype type=select field=meta method=serialize
        @caption Puu tüüp

	@property icon_folder_open type=objpicker clid=CL_ICON field=meta method=serialize
	@caption Lahtise kausta ikoon

	@property icon_folder_closed type=objpicker clid=CL_ICON field=meta method=serialize
	@caption Kinnise kausta ikoon
*/
class treeview extends aw_template
{
	function treeview($args = array())
	{
		$this->init(array(
			"tpldir" => "treeview",
			"clid" => CL_TREEVIEW,
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
		$this->urltemplate = $args["urltemplate"];
		$this->meta = $obj["meta"];
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
			"rootname" => ($obj["meta"]["rootcaption"]) ? $obj["meta"]["rootcaption"] : $rootobj["name"],
			"rooturl" => $this->do_item_link($rootobj),
			"icon_root" => ($obj["meta"]["icon_root"])? $this->mk_my_orb("show",array("id" => $obj["meta"]["icon_root"]),"icons") : "/automatweb/images/aw_ikoon.gif",
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
				// that's used for objects only
				if ($row["icon_id"] > 0)
				{
					$icon_id = $row["icon_id"];
				}
				elseif ($this->meta["icon_folder_closed"])
				{
					$icon_id = $this->meta["icon_folder_closed"];
				};
				if ($icon_id)
				{
					$icon_url = $this->mk_my_orb("show",array("id" => $icon_id),"icons");
				}
				else
				{
					$icon_url = $baseurl . "/automatweb/images/ftv2doc.gif";
				};
				$url = $this->do_item_link($row);
				$this->vars(array(
					"name" => $row["name"],
					"id" => $row["oid"],
					"parent" => $row["parent"],
					"iconurl" => $icon_url,
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

	function do_item_link($row)
	{
		if ($row["link"])
		{
			$url = $row["link"];
		}
		elseif ($this->urltemplate)
		{
			$url = sprintf($this->urltemplate,$row["oid"]);
		}
		else
		{
			$url = $this->cfg["baseurl"] . "/" . $row["oid"];
		};
		return $url;
	}

};
?>
