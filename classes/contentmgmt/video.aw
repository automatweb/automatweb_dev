<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/video.aw,v 1.5 2005/06/20 14:55:45 duke Exp $
// video.aw - Video 
/*

@classinfo syslog_type=ST_VIDEO relationmgr=yes no_comment=1 no_status=1


@default table=objects
@default group=general

@default field=meta
@default method=serialize

@property image type=releditor reltype=RELTYPE_IMAGE use_form=emb rel_id=first
@caption Pilt

@property caption type=textarea rows=3 cols=20
@caption Allkiri

@property author type=textbox
@caption Autor

@property origin type=textbox
@caption Allikas

@property origin_url type=textbox
@caption Allika URL

@property date type=date_select
@caption Kuup&auml;ev

@property src_rp type=textbox
@caption URL (RealPlayer)

property capt_rp type=textbox
caption Lingi tekst (RealPlayer)

@property src_wm type=textbox
@caption URL (Windows Media)

property capt_wm type=textbox
caption Lingi tekst (Windows Media)

@property trans type=translator group=trans props=name
@caption T&otilde;ge

@groupinfo trans caption="T&otilde;lkimine"

@reltype IMAGE value=1 clid=CL_IMAGE
@caption Video pilt

*/

class video extends class_base
{
	function video()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/video",
			"clid" => CL_VIDEO
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");

		$im = get_instance(CL_IMAGE);

		$image = "";
		$imc = reset($ob->connections_from(array("type" => "RELTYPE_IMAGE")));
		if ($imc)
		{
			$imid = $imc->prop("to");
			$image = $im->make_img_tag($im->get_url_by_id($imid));
		}

		$this->vars(array(
			"name" => $ob->prop("name"),
			"image" => $image,
			"caption" => $ob->prop("caption"),
		));

		$dat = array(
			array("src_rp", "capt_rp", "HAS_RP"),
			array("src_wm", "capt_wm", "HAS_WM"),
		);

		foreach($dat as $format)
		{
			if ($ob->prop($format[0]))
			{
				$this->vars(array(
					"vid_url" => $ob->prop($format[0]),
					//"vid_url_capt" => $ob->prop($format[1]),
					"vid_url_capt" => $ob->name(),
				));
				$this->vars(array(
					$format[2] => $this->parse($format[2])
				));
			}
		}
	
		return $this->parse();
	}
	
	function request_execute($o)
	{
		$this->read_template("autoplay.tpl");
		$this->vars($o->properties());
		die($this->parse());
	}
}
?>
