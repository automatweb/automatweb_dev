<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/newsfeed.aw,v 1.6 2005/02/28 15:28:36 duke Exp $
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

@property limittype type=chooser orient=vertical
@caption Milliseid uudiseid näidata?

@property count type=textbox size=2
@caption Mitu viimast

@property days type=textbox size=2
@caption Mitme viimase päeva omad

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
					"rss20" => t("RSS 2.0"),
					"atom" => t("Atom (implementeerimata)"),
				);
				break;

			case "limittype":
				$prop["options"] = array(
					"last" => "Viimased X uudist",
					"days" => "Viimase X päeva uudised",
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


		$limittype = $feedobj->prop("limittype") == "days" ? "days" : "last";

		if (sizeof($parents) > 0)
		{
			$count = $feedobj->prop("count");
			if ($count < 1 || $count > 20)
			{
				$count = 20;
			};
			$ol_args = array(
				"class_id" => $classes,
				"parent" => $parents,
				"status" => STAT_ACTIVE,
				"sort_by" => "objects.modified DESC",
			);
			if ($limittype == "last")
			{
				$ol_args["limit"] = $count;
			};
			if ($limittype == "days")
			{
				$days = $feedobj->prop("days");
				$start = strtotime("-${days} days");
				$ol_args["CL_DOCUMENT.doc_modified"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start);
			};
			$ol = new object_list($ol_args);
			$first = 0;
			$source = aw_ini_get("newsfeed.source");
			$baseurl = aw_ini_get("baseurl");
			foreach($ol->arr() as $o)
			{
				//$mod_date = $o->modified();
				$mod_date = $o->prop("doc_modified");
				if ($first == 0)
				{
					$first = $mod_date;
				};
				$oid = $o->id();
				$art_lead = $o->prop("lead");
				$description = $o->prop("content");
				$al->parse_oo_aliases($oid,$art_lead);
				$al->parse_oo_aliases($oid,$description);
				$items[] = array(
					"item_id" => $oid,
					"title" => $o->name(),
					"link" => $baseurl . "/" . $oid,
					"artdate" => date("Y-m-d",$mod_date),
					"start_date" => date("Y-m-d H:i:s",$mod_date),
					"end_date" => "0000-00-00 00:00:00", // documents have no ending date
					"author" => $o->prop("author"),
					"source" => $source,
					"art_lead" => $art_lead,
					"description" => $description,
					"guid" => $baseurl . "/" . $oid,
					"pubDate" => date("r",$o->prop("doc_modified")),
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
