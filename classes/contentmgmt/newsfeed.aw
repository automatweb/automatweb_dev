<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/newsfeed.aw,v 1.2 2005/02/09 10:27:13 duke Exp $
// newsfeed.aw - Newsfeed 
/*

@classinfo syslog_type=ST_NEWSFEED relationmgr=yes

@default table=objects
@default group=general

@property alias type=textbox
@caption Alias
@comment Selle abil saab fiidile otse ligi

@default field=meta
@default method=serialize

@property feedtype type=chooser orient=vertical
@caption Tüüp

@property count type=textbox size=2
@caption Mitu viimast

@reltype FEED_SOURCE value=1 clid=CL_MENU
@caption Materjalide kaust

*/

class newsfeed extends class_base
{
	function newsfeed()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "contentmgmt/newsfeed",
			"clid" => CL_NEWSFEED
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "feedtype":
				$prop["options"] = array(
					"rss20" => "RSS 2.0",
					"atom" => "Atom (implementeerimata)",
				);
				break;


		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function request_execute($feedobj)
	{
		$parents = array();
		$classes = array(CL_DOCUMENT);
		$sources = $feedobj->connections_from(array(
			"type" => "RELTYPE_FEED_SOURCE",
		));
		foreach($sources as $source)
		{
			$parents[] = $source->prop("to");
		};
		$items = array();
		//arr($o->properties());
		$res = array();

		$al = get_instance("aliasmgr");

		if (sizeof($parents) > 0)
		{
			$count = $feedobj->prop("count");
			if ($count < 1 || $count > 20)
			{
				$count = 20;
			};
			$ol = new object_list(array(
				"class_id" => $classes,
				"parent" => $parents,
				"status" => STAT_ACTIVE,
				"sort_by" => "objects.modified DESC",
				"limit" => $count,
			));
			$first = 0;
			foreach($ol->arr() as $o)
			{
				if ($first == 0)
				{
					$first = $o->modified();
				};
				$oid = $o->id();
				$art_lead = $o->prop("lead");
				$description = $o->prop("content");
				$al->parse_oo_aliases($oid,$art_lead);
				$al->parse_oo_aliases($oid,$description);
				$items[] = array(
					"item_id" => $oid,
					"title" => $o->name(),
					"link" => aw_ini_get("baseurl") . "/" . $oid,
					"artdate" => date("Y-m-d",$o->modified()),
					"start_date" => date("Y-m-d H:i:s",$o->modified()),
					"end_date" => "0000-00-00 00:00:00", // documents have no ending date
					"author" => $o->prop("author"),
					"source" => $o->prop("source"),
					"art_lead" => $art_lead,
					"description" => $description,
					"guid" => aw_ini_get("baseurl") . "/" . $oid,
					"pubDate" => date("r",$o->modified()),
				);	
			};
		};
		$data = array(
			"channeldata" => array(
				"title" => $feedobj->name(),
				"link" => aw_ini_get("baseurl"),
				"description" => $feedobj->comment(),
				"language" => $feedobj->lang(),
				"LastBuildDate" => date("r",$first),
			),
			"items" => $items,
		);
		switch($feedobj->prop("feedtype"))
		{
			case "wtf":
				print "just go away";
			
			default:
				header("Content-type: text/xml");
				print $this->rss20_encode($data);
		};
		exit;


	}

	function rss20_encode($data)
	{
		$res = "<?xml version='1.0' encoding='ISO-8859-1'?>\n";
		$res .= '<rss version="2.0">' . " \n";
		$res .= "\t<channel>\n";

		foreach($data["channeldata"] as $key => $val)
		{
			$val = trim($val);
			/*
			if (!is_numeric($val))
			{
				$val = $this->_encode_rss_string($val);
			};
			*/
			$res .= "\t\t<${key}>" . $val . "</${key}>\n";
		};

		$encoded_attribs = array("title","link","author","source","art_lead","description","guid");

		foreach($data["items"] as $item)
		{
			$res .= "\t\t<item>\n";
			foreach($item as $key => $val)
			{
				$val = trim($val);
				if (in_array($key,$encoded_attribs))
				{
					$val = $this->_encode_rss_string($val);
				};
				$res .= "\t\t\t<${key}>" . $val . "</${key}>\n";
			};
			$res .= "\t\t</item>\n";
		};
		$res .= "\t</channel>\n";
		$res .= "</rss>\n";
		return $res;
	}

	function _encode_rss_string($src)
	{
		return "<![CDATA[" . nl2br($src) . "]]>";
	}
}
?>
