<?php
// $Id: site_threepane.aw,v 1.3 2002/11/07 10:52:36 kristo Exp $
// site_threepane.aw - simpel 3 paaniga sait.
/*
	@default table=objects
	@default group=general
	@property frameset type=objpicker clid=CL_FRAMESET field=meta method=serialize
	@caption Frameseti objekt

	@property treeview type=objpicker clid=CL_TREEVIEW field=meta method=serialize
	@caption Puu objekt

	@property logo type=imgupload field=meta method=serialize
	@caption Logo

	@property info type=array getter=callback_get_info field=meta method=serialize
	@caption Metainfo

	@property preview type=text
	@caption Näita

*/
class site_threepane extends aw_template
{
	function site_threepane($args = array())
	{
		$this->init(array(
			"tpldir" => "site/threepane",
			"clid" => CL_SITE_THREEPANE,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		switch($data["name"])
		{
			case "logo":
				$data["value"] = $args["obj"]["meta"]["logo_url"] != "" ? "<img src='".$args[obj][meta][logo_url]."'>" : "";
				$data["value"] .= "<br>";
				break;

			case "preview":
				classload("html");
				$id = $args["obj"]["oid"];
				if ($id)
				{
					$data["value"] = html::href(array("url" => $this->cfg["baseurl"] . "/orb.aw?class=site_threepane&action=show&id=$id","caption" => "Näita saiti","target" => "_blank"));
				};
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
		$fr = get_instance("vcl/frameset");
		$frdata = $this->get_object($obj["meta"]["frameset"]);
		$frmeta = $frdata["meta"];
		// first: load and generate the frameset
		switch($type)
		{
			case "top":
				$this->read_template("top.tpl");
				classload("html");
				$this->vars(array(
					"logo" => html::img(array("url" => $obj["meta"]["logo_url"])),
				));
				$info = $obj["meta"]["info"];
				$max = sizeof($info["name"]);
				$vars = array();
				for ($i = 0; $i < $max; $i++)
				{
					$varname = $info["name"][$i];
					$varvalue = $info["content"][$i];
					$vars[$varname] = $varvalue;
				};
				$this->vars($vars);
				$content = $this->parse();
				$htmlpage = get_instance("vcl/page");
				$res = $htmlpage->show(array(
					"id" => $frmeta["framedata"]["top"]["style"],
					"content" => $content,
				));
				return $res;
				break;
			case "left":
				$treeview = get_instance("vcl/treeview");
				$styl = $frmeta["framedata"]["right"]["style"];
				$content = $treeview->show(array(
						"id" => $obj["meta"]["treeview"],
						"urltemplate" => "/orb.aw?class=page&action=show&id=$styl&section=%s",
				));
				$htmlpage = get_instance("vcl/page");
				$res = $htmlpage->show(array(
					"id" => $frmeta["framedata"]["left"]["style"],
					"content" => $content,
				));
				return $res;
				break;

			case "right":
				$content = "no content";
				$htmlpage = get_instance("vcl/page");
				$res = $htmlpage->show(array(
					"id" => $frmeta["framedata"]["right"]["style"],
					"content" => $content,
				));
				return $res;

				break;

			default:
				$tv = get_instance("vcl/treeview");
				$styl = $frmeta["framedata"]["right"]["style"];
				$tv->urltemplate = "/orb.aw?class=page&action=show&id=$styl&section=%s";
				$treeobj_id = $this->get_object($obj["meta"]["treeview"]);
				$treeobj = $this->get_object($treeobj_id);
				$rootobj_id = $treeobj["meta"]["root"];
				$rootobj = $this->get_object($rootobj_id);
				$sources = array(
					"top" => $this->mk_my_orb("show",array("id" => $id,"type" => "top")),
					"left" => $this->mk_my_orb("show",array("id" => $id,"type" => "left")),
					"right" => $tv->do_item_link($rootobj),
				);
				$res = $fr->show(array(
						"id" => $obj["meta"]["frameset"],
						"sources" => $sources,
				));
				print $res;
				exit;
		};
	}

	function callback_get_info($args = array())
	{
		$data = $args["prop"];
		static $i = 0;
		$max = 0;
		$naxnax = new aw_array($data["value"]["name"]);
		foreach($naxnax->get() as $key => $val)
		{
			if ($val)
			{
				$max++;
			};
		};
//                $max = sizeof($data["value"]["name"]);
		if ($i < ($max + 1))
		{	
			if ($data["value"]["name"][$i] || ($i == $max))
			{
				$node["caption"] = "Metainfo";
				$node["items"] = array();

				$tmp = array(
					"type" => "textbox",
					"name" => "info[name][$i]",
					"size" => 25,
					"value" => $data["value"]["name"][$i],
				);
				array_push($node["items"],$tmp);
				$tmp = array(
					"type" => "textbox",
					"name" => "info[content][$i]",
					"size" => 25,
					"value" => $data["value"]["content"][$i],
				);
				array_push($node["items"],$tmp);
			}
			else
			{
				$node = array();
			};
			$i++;
		}
		else
		{
			$node = false;
		};
		return $node;
	}
};
?>
