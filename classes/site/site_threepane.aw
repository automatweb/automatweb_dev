<?php
// $Id: site_threepane.aw,v 1.11 2005/03/02 13:11:37 kristo Exp $
// site_threepane.aw - simpel 3 paaniga sait.
/*
	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property frameset type=relpicker reltype=RELTYPE_SITE_FRAMESET
	@caption Frameseti objekt

	@property treeview type=relpicker reltype=RELTYPE_SITE_TREEVIEW
	@caption Puu objekt

	@property logo type=relpicker reltype=RELTYPE_SITE_LOGO
	@caption Logo

	@property info type=generated generator=callback_get_info 
	@caption Metainfo

	@property preview editonly=1 type=text 
	@caption Näita

	@classinfo relationmgr=yes syslog_type=ST_SITE_THREEPANE

	@reltype SITE_FRAMESET value=1 clid=CL_FRAMESET
	@caption Frameset

	@reltype SITE_TREEVIEW value=2 clid=CL_TREEVIEW
	@caption Puu seadistused

	@reltype SITE_LOGO value=3 clid=CL_IMAGE
	@caption Logo
	

*/
class site_threepane extends class_base
{
	function site_threepane($args = array())
	{
		$this->init(array(
			"tpldir" => "site/threepane",
			"clid" => CL_SITE_THREEPANE,
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "preview":
				$id = $arr["obj_inst"]->id();
				// no need to check whether id exists, since this only called
				// for existing objects
				$data["value"] = html::href(array(
					"url" => $this->cfg["baseurl"] . "/orb.aw?class=site_threepane&action=show&id=$id",
					"caption" => "Näita saiti",
					"target" => "_blank",
				));
				$retval = PROP_IGNORE;
				break;
		};
		return $retval;
	}

	/**  
		
		@attrib name=show params=name default="0"
		
		@param id required
		@param type optional
		
		@returns
		
		
		@comment

	**/
	function show($args = array())
	{
		extract($args);
		$obj = new object($id);

		$fr = get_instance(CL_FRAMESET);
		$frdata = $obj->prop("frameset");

		$frobject = new object($obj->prop("frameset"));
		$frmeta = $frobject->meta();
		
		// first: load and generate the frameset
		switch($type)
		{
			case "top":
				$this->read_template("top.tpl");
				$img = get_instance(CL_IMAGE);
				$imgdata = $img->get_image_by_id($obj->prop("logo"));
				$this->vars(array(
					"logo" => html::img(array("url" => $imgdata["url"])),
				));
				$info = $obj->prop("info");
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
						"id" => $obj->prop("treeview"),
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
				$treeobj = new object($obj->prop("treeview"));
				$rootobj = new object($treeobj->prop("root"));
				$sources = array(
					"top" => $this->mk_my_orb("show",array("id" => $id,"type" => "top")),
					"left" => $this->mk_my_orb("show",array("id" => $id,"type" => "left")),
					"right" => $tv->do_item_link($rootobj),
				);
				$res = $fr->show(array(
						"id" => $obj->prop("frameset"),
						"sources" => $sources,
						"title" => $obj->name(),
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

	function request_execute($obj)
	{
		return $this->show(array(
			"id" => $obj->id()
		));
	}
};
?>
