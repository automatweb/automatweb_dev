<?php
// $Id: site_threepane.aw,v 1.1 2002/11/04 17:48:06 duke Exp $
// site_threepane.aw - simpel 3 paaniga sait.
/*
	@default table=objects
	@default group=general
	@property frameset type=select field=meta method=serialize
	@caption Frameseti objekt

	@property treeview type=select field=meta method=serialize
	@caption Puu objekt

	@property logo type=imgupload field=meta method=serialize
	@caption Logo

	@property preview type=text
	@caption Näita

*/
class site_threepane extends aw_template
{
	function site_threepane($args = array())
	{
		$this->init("site/threepane");
	}

	function add($args = array())
	{
		extract($args);
                // gotta get rid of that too
                $cfg = get_instance("cfg/cfgmanager");
                return $cfg->change(array(
                        "class_id" => CL_SITE_THREEPANE,
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
			case "treeview":
				$data["options"] = $this->list_objects(array("class" => CL_TREEVIEW, "addempty" => true));
				break;
			
			case "frameset":
				$data["options"] = $this->list_objects(array("class" => CL_FRAMESET, "addempty" => true));
				break;

				
			case "logo":
				$data["value"] = $args["obj"]["meta"]["logo_url"] != "" ? "<img src='".$args[obj][meta][logo_url]."'>" : "";
				$data["value"] .= "<br>";
				break;

			case "preview":
				classload("html");
				$id = $args["obj"]["oid"];
				$data["value"] = html::href(array("url" => $this->cfg["baseurl"] . "/orb.aw?class=site_threepane&action=show&id=$id","caption" => "Näita saiti","target" => "_blank"));
				break;
		};
	}

	function show($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		// let's show the site
		if ($obj["class_id"] != CL_SITE_THREEPANE)
		{
			die("what are you up to?");
		};
		// first: load and generate the frameset
		switch($type)
		{
			case "top":
				$this->read_template("top.tpl");
				classload("html");
				$this->vars(array(
					"logo" => html::img(array("url" => $obj["meta"]["logo_url"])),
				));
				return $this->parse();
				break;
			case "left":
				$treeview = get_instance("vcl/treeview");
				return $treeview->show(array("id" => $obj["meta"]["treeview"]));
				break;

			case "right":
				return "no content";
				break;

			default:
				$fr = get_instance("vcl/frameset");
				$sources = array(
					"top" => $this->mk_my_orb("show",array("id" => $id,"type" => "top")),
					"left" => $this->mk_my_orb("show",array("id" => $id,"type" => "left")),
					"right" => $this->mk_my_orb("show",array("id" => $id,"type" => "right")),
				);
				$res = $fr->show(array(
						"id" => $obj["meta"]["frameset"],
						"sources" => $sources,
				));
				print $res;
				exit;
		};
	}
};
?>
