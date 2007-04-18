<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/newsfeed.aw,v 1.20 2007/04/18 12:39:59 kristo Exp $
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
	@caption T��p

	@property limittype type=chooser orient=vertical
	@caption Milliseid uudiseid n�idata?

	@property count type=textbox size=2
	@caption Mitu viimast

	@property days type=textbox size=2
	@caption Mitme viimase p�eva omad

	@property sort_by type=select 
	@caption Mille j&auml;rgi sorteeritakse

	@property sort_ord type=select 

	@property parse_embed type=checkbox ch_value=1 default=1
	@caption N�ita ka lisatud objekte

@default group=folders

	@property folders type=table store=no 
	@caption Kaustade seaded

@default group=kw

	@property kw type=table store=no
	@caption Milliste m&auml;rk&otilde;nadega dokumente n&auml;idata

@groupinfo folders caption="Kaustad"
@groupinfo kw caption="M&auml;rks&otilde;nad"

@reltype FEED_SOURCE value=1 clid=CL_MENU
@caption Materjalide kaust

*/

class newsfeed extends class_base
{
	function newsfeed()
	{
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
			case "sort_ord":
				$prop['options'] = array(
					'DESC' => t("Suurem (uuem) enne"),
					'ASC' => t("V&auml;iksem (vanem) enne"),
				);
				break;

			case "sort_by":
				$prop['options'] = array(
					'objects.jrk' => t("J&auml;rjekorra j&auml;rgi"),
					'objects.created' => t("Loomise kuup&auml;eva j&auml;rgi"),
					'objects.modified' => t("Muutmise kuup&auml;eva j&auml;rgi"),
					'documents.modified' => t("Dokumenti kirjutatud kuup&auml;eva j&auml;rgi"),
					'objects.name' => t("Objekti nime j&auml;rgi"),
					'planner.start' => t("Kalendris valitud aja j&auml;rgi"),
					'RAND()' => t("Random"),
				);
				break;

			case "kw":
				$this->_kw($arr);
				break;

			case "feedtype":
				$prop["options"] = array(
					"rss20" => t("RSS 2.0"),
					"atom" => t("Atom (implementeerimata)"),
				);
				break;

			case "limittype":
				$prop["options"] = array(
					"last" => "Viimased X uudist",
					"days" => "Viimase X p�eva uudised",
				);
				break;

			case "folders":
				$this->do_folders_table($arr);
				break;


		};
		return $retval;
	}

	function do_folders_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Name"),
		));
		$t->define_field(array(
			"name" => "include_subs",
			"caption" => t("V�ta alamkaustadest ka"),
			"align" => "center",
			"width" => 150,
		));

		$conns = $arr["obj_inst"]->connections_from(array(
			"reltype" => "RELTYPE_FEED_SOURCE",
		));

		$include_subs = $arr["obj_inst"]->meta("include_subs");

		foreach($conns as $conn)
		{
			$c_id = $conn->prop("to");
			$t->define_data(array(
				"id" => $c_id,
				"name" => $conn->prop("to.name"),
				"include_subs" => html::checkbox(array(
					"name" => "include_subs[${c_id}]",
					"value" => 1,
					"checked" => $include_subs[$c_id],
				)),
			));
		};
	}

	function save_folders($arr)
	{
		$arr["obj_inst"]->set_meta("include_subs",$arr["request"]["include_subs"]);
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "folders":
				$this->save_folders($arr);
				break;

			case "kw":
				$this->_save_kw($arr);
				break;
		}
		return $retval;
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
		$include_subs = $feedobj->meta("include_subs");
		foreach($sources as $source)
		{
			$src_folder = $source->prop("to");
			$parents[] = $src_folder;
			if ($include_subs[$src_folder])
			{
				$tree = new object_tree(array(
					"parent" => $src_folder,
					"class_id" => CL_MENU,
					"site_id" => array(),
				));
				$items = $tree->to_list();
				$parents = $parents + $items->ids();
			};
		};

		$items = array();
		//arr($o->properties());
		$res = array();

		$al = get_instance("alias_parser");


		$limittype = $feedobj->prop("limittype") == "days" ? "days" : "last";

		$use_kws = safe_array($feedobj->meta("use_kws"));

		if (sizeof($parents) > 0 || count($use_kws) > 0)
		{
			$count = $feedobj->prop("count");
			if ($count < 1 || $count > 20)
			{
				$count = 20;
			};

			$kwlist = array();
			if (count($use_kws))
			{
				$kw_ol = new object_list(array(
					"oid" => array_keys($use_kws),
					"lang_id" => array(),
					"site_id" => array()
				));
				$kwlist = $kw_ol->names();

				$c = new connection();
				$doclist = $c->find(array(
					"to" => $kw_ol->ids(),
				));
				$docid = array();
				$non_docid = array();
				foreach($doclist as $con)
				{
					if ($con["from.class_id"] == CL_DOCUMENT)
					{
						if ($con["from.status"] == STAT_ACTIVE)
						{
							$docid[$con["from"]] = $con["from"];
						}
					}	
					else
					{
						$non_docid[$con["from"]] = $con["from"];
					}
				}

				if (count($non_docid))
				{
					// fetch docs connected to THOSE
					$doclist = $c->find(array(
						"from.class_id" => CL_DOCUMENT,
						"to" => $non_docid
					));
					foreach($doclist as $con)
					{
						if ($con["from.status"] == STAT_ACTIVE)
						{
							$docid[$con["from"]] = $con["from"];
						}
					}
				}
			}

			$cond = array(
				"parent" => $parents,
			);
			if (count($docid))
			{
				$cond["oid"] = $docid;
			}

			$_ob = $feedobj->prop("sort_by")." ".$feedobj->prop("sort_ord");
			if ($feedobj->prop("sort_by") == "documents.modified")
			{
				$_ob .= " ,objects.created DESC";
			};

			$ol_args = array(
				"class_id" => $classes,
				"status" => STAT_ACTIVE,
				"sort_by" => $_ob,
				new object_list_filter(array(
					"logic" => "OR",
					"conditions" => $cond
				)),
				new object_list_filter(array("non_filter_classes" => CL_DOCUMENT))
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
			$parse_embed = $feedobj->prop("parse_embed");
			foreach($ol->arr() as $o)
			{
				$mod_date = $o->prop("doc_modified");
				if ($mod_date < 300)
				{
					$mod_date = $o->modified();
				}
				if ($first == 0)
				{
					$first = $mod_date;
				};
				$oid = $o->id();
				$art_lead = $o->prop("lead");
				$description = $o->prop("content");
				if (1 == $parse_embed)
				{
					$si = __get_site_instance();
					if ($si)
					{
						$si->parse_document_new($o);
						$art_lead = $o->prop("lead");
						$description = $o->prop("content");
					}

					$al->parse_oo_aliases($oid,$art_lead);
					$al->parse_oo_aliases($oid,$description);
				}
				else
				{
					$art_lead = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$art_lead);
					$description = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",$description);

					$art_lead = preg_replace("/#d#(.*)#\/d#/imsU","\\1",$art_lead);
					$description = preg_replace("/#d#(.*)#\/d#/imsU","\\1",$description);

				};
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
					"pubDate" => date("r",$mod_date),
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
		$res = str_replace("<br />","",$res);
		return $res;
	}

	function _encode_rss_string($src)
	{
		return "<![CDATA[" . nl2br($src) . "]]>";
	}

	function _init_kw_t(&$t)
	{
		$t->define_field(array(
			"name" => "kw",
			"caption" => t("M&auml;rks&otilde;na"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "use",
			"caption" => t("Vali"),
			"align" => "center"
		));
	}

	function _kw($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_kw_t($t);

		$use = $arr["obj_inst"]->meta("use_kws");

		$kws = new object_list(array(
			"class_id" => CL_KEYWORD,
		));
		foreach($kws->arr() as $kwid => $kw)
		{
			$t->define_data(array(
				"kw" => html::obj_change_url($kw),
				"use" => html::checkbox(array(
					"name" => "use[$kwid]",
					"value" => 1,
					"checked" => $use[$kwid] == 1
				))
			));
		}
		$t->set_default_sortby("kw");
		$t->sort_by();
	}

	function _save_kw($arr)
	{
		$arr["obj_inst"]->set_meta("use_kws", safe_array($arr["request"]["use"]));
	}
}
?>
