<?php
// $Id: site_threepane.aw,v 1.5 2002/11/14 16:04:18 duke Exp $
// site_threepane.aw - simpel 3 paaniga sait.
/*
	@default table=objects
	@default group=general

	@property frameset type=objpicker clid=CL_FRAMESET field=meta method=serialize
	@caption Frameseti objekt

	@property treeview type=objpicker clid=CL_TREEVIEW field=meta method=serialize
	@caption Puu objekt

	@property logo type=relpicker clid=CL_IMAGE field=meta method=serialize
	@caption Logo

	@xproperty logo type=imgupload field=meta method=serialize
	@xcaption Logo

	@property info type=generated generator=callback_get_info field=meta method=serialize
	@caption Metainfo

	@property preview editonly=1 type=text
	@caption Näita

	@classinfo relationmgr=yes

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
			case "preview":
				classload("html");
				$id = $args["obj"]["oid"];
				// no need to check whether id exists, since this only called
				// for existing objects
				$data["value"] = html::href(array("url" => $this->cfg["baseurl"] . "/orb.aw?class=site_threepane&action=show&id=$id","caption" => "Näita saiti","target" => "_blank"));
				break;
		};
	}

	function show($args = array())
	{
		extract($args);
		$obj = $this->get_object(array(
			"oid" => $id,
			"class_id" => CL_SITE_THREEPANE,
		));

		$fr = get_instance("vcl/frameset");
		$frdata = $this->get_object($obj["meta"]["frameset"]);
		$frmeta = $frdata["meta"];

		// first: load and generate the frameset
		switch($type)
		{
			case "top":
				$this->read_template("top.tpl");
				classload("html");
				$img = get_instance("image");
				$imgdata = $img->get_image_by_id($obj["meta"]["logo"]);
				$this->vars(array(
					"logo" => html::img(array("url" => $imgdata["url"])),
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
						"title" => $obj["name"],
				));
				print $res;
				exit;
		};
	}

	////
	// !Called from class_base, generates a list for entering metainfo
	function callback_get_info($args = array())
	{
		$data = $args["prop"];
		$names = new aw_array($data["value"]["name"]);
		$content = new aw_array($data["value"]["content"]);
		$nodes = array();
		$max = 0;
		foreach($names->get() as $key => $name)
		{
			$max = $key;
			$val = $content->get_at($key);
			if ($val)
			{
				$nodes[] = $this->_gen_line($key,$name,$val);
			};
		};
		$max++;
		$nodes[] = $this->_gen_line($max,"","");
		return $nodes;
	}

	function _gen_line($key,$name,$value)
	{
		return array(
			"caption" => "Metainfo",
			"items" => array(
				array(
					"type" => "textbox",
					"name" => "info[name][$key]",
					"size" => 25,
					"value" => $name,
				),
				array(
					"type" => "textbox",
					"name" => "info[content][$key]",
					"size" => 25,
					"value" => $value,
				),
			),
		);
	}
};
?>
