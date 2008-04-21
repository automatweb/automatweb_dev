<?php
// json_delfi.aw - Delfi JSON väljund
/*

@classinfo syslog_type=ST_JSON_DELFI relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@default field=meta
@default method=serialize

@property json_url type=textbox
@caption V&auml;ljundi URL

@property photo_dir_url type=textbox
@caption Piltide kausta URL

@property json_lang type=textbox
@caption Keel

@property categories type=select multiple=1
@caption Kategooriad

@property attrs_event type=select multiple=1
@caption S&uuml;ndmuste atribuudid

@property attrs_loc type=select multiple=1
@caption Toimumiskoha atribuudid

@property attrs_org type=select multiple=1
@caption Korraldaja atribuudid

@property show_output type=text
@caption N&auml;ita v&auml;ljundit

*/

class json_delfi extends class_base
{
	function json_delfi()
	{
		$this->init(array(
			"tpldir" => "applications/event_import_2/json_delfi",
			"clid" => CL_JSON_DELFI
		));
	}

	function get_property($arr)
	{
		$this->temporary_function($arr);
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "attrs_event":
			case "attrs_loc":
				if(strlen($arr["obj_inst"]->json_url) > 0)
				{
					$url = $arr["obj_inst"]->json_url."search.php?lang=est&partner=delfi&action=GetAttributes";
					foreach($this->get_json_output($url) as $c)
					{
						$prop["options"][$c->attr_id] = $c->attr_title;
					}
				}
				break;

			case "categories":
				if(strlen($arr["obj_inst"]->json_url) > 0)
				{
					$url = $arr["obj_inst"]->json_url."search.php?lang=est&partner=delfi&action=GetAttributeValues&attr_id=21";
					foreach($this->get_json_output($url) as $c)
					{
						$prop["options"][$c->attrval_id] = $c->attrval_title;
					}
				}
				break;
		}

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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	/**
		@attrib name=make_master_import_happy

		@param id required type=oid
	**/
	function make_master_import_happy($arr)
	{
		$r = array();
		$o = obj($arr["id"]);
		$r["location"] = $this->get_it($arr, 2, $o->attrs_loc);
		$r["event"] = $this->get_it($arr, 1, $o->attrs_event);
		$r["organizer"] = $this->get_it($arr, 5, $o->attrs_org);
		return $r;
	}

	private function get_it($arr, $type, $attrs = array())
	{
		$r = array();
		$cnt = 1;
		$i = 0;
		$o = obj($arr["id"]);
		while($cnt != 0)
		{
			$url = $o->json_url;
			$url .= "search.php?lang=est&partner=delfi&action=RunSearchQuery&object_types[]=".$type."&start=".$i."&limit=".($i + 1000);
			// Can't get display_attributes to work.
			foreach($attrs as $da)
			{
				$url .= "&display_attributes[]=".$da;
			}
			$cnt = 0;
			$i += 1000;
			$input = file_get_contents($url);
			foreach(json_decode($input)->objects as $l)
			{
				$cnt++;
				$t = array();
				foreach($l->attributes as $a)
				{
					$_t = array();
					switch ($a->attr_id)
					{					
						// Koht		-- equivalent to our event_time. -kaarel
						case "14":
							$_t["location"] = $a->obj_id;
							$_t["start"] = $this->mk_my_time($a->start_time);
							$_t["end"] = $this->mk_my_time($a->end_time);
							$_t["jrk"] = $a->ui_index;
							$_t["ext_id"] = $a->attrval_id;
							$t["event_time"][] = $_t;
							break;

						// Yrituse tyyp
						case "21":
							$_t["tegevusala"] = $a->attrval_title;
							$_t["ext_id"] = $a->attrval_id;
							$_t["jrk"] = $a->ui_index;
							$r["sector"][$a->attrval_id] = $_t;
							$t["sector"][] = $a->attrval_id;
							break;

						// Kirjeldus
						case "33":
							$t["description"] = $a->attrval_title;
							break;

						// Pilt
						case "35":
							$_t["ext_id"] = $a->attrval_id;
							$_t["small"] = $o->photo_dir_url.$l->obj_id."/thumbs/".$a->attrval_title;
							$_t["big"] = $o->photo_dir_url.$l->obj_id."/".$a->attrval_title;
							$_t["name"] = $a->attrval_title;
							$_t["jrk"] = $a->ui_index;
							$t["photo"][] = $_t;
							break;

						// Geograafiline piirkond
						case "37":
							// Can't put it anywhere else, cause it might be county, might be city or just some little place.
							$_t["comment"] = $a->attrval_title;
							$_t["name"] = $a->attrval_title;
							$_t["ext_id"] = $a->attrval_id;
							$_t["jrk"] = $a->ui_index;
							$t["address"][] = $_t;
							break;

						// Korraldaja
						case "92":
							$t["organizer"][] = $a->obj_id;
							break;

						// Pealkiri
						case "120":
							$t["name"] = $a->attrval_title;
							break;
							
						// Aadress
						case "137":
							$_t["aadress"] = $a->attrval_title;
							$_t["ext_id"] = $a->attrval_id;
							$_t["jrk"] = $a->ui_index;
							$t["address"][] = $_t;
							break;

						// Koduleht
						case "146":
							// Actually it's e-mail address.
							if(ereg("mailto:", $a->attrval_title))
							{
								list($ml, $caption) = explode("|", $$a->attrval_title);
								$_t["name"] = $caption;
								$_t["mail"] = $ml;
								$_t["jrk"] = $a->ui_index;
								$_t["ext_id"] = $a->attrval_id;
								$t["email"][] = $_t;
							}
							else
							{
								list($url, $caption) = explode("|", $$a->attrval_title);
								$_t["name"] = $caption;
								$_t["url"] = $url;
								$_t["jrk"] = $a->ui_index;
								$_t["ext_id"] = $a->attrval_id;
								$t["url"][] = $_t;
							}
							break;

						// Kontakt
						case "151":
							break;

						// Avatud
						case "152":
							break;

						// Teenused
						case "153":
							break;

						// Restorani stiil
						case "158":
							break;

						// Elav muusika
						case "159":
							break;
						
						// Telefon
						case "161":
							$_t["name"] = $a->attrval_title;
							$_t["ext_id"] = $a->attrval_id;
							$_t["jrk"] = $a->ui_index;
							$t["phone"][] = $t;
							break;

						// Faks
						case "195":
							$_t["type"] = "fax";
							$_t["name"] = $a->attrval_title;
							$_t["ext_id"] = $a->attrval_id;
							$_t["jrk"] = $a->ui_index;
							$t["phone"][] = $t;
							break;

						// E-post
						case "197":
							$_t["mail"] = $a->attrval_title;
							$_t["jrk"] = $a->ui_index;
							$_t["ext_id"] = $a->attrval_id;
							$t["email"][] = $_t;
							break;
					}
				}
				$t["ext_id"] = $l->obj_id;
				$r[$l->obj_id] = $t;
			}
		}
		return $r;
	}

	private function get_json_output($url)
	{
		$input = file_get_contents($url);
		return json_decode($input);
	}

	private function mk_my_time($d)
	{
		// 2008-04-13 10:00:00
		return mktime(substr($d, 11, 2), substr($d, 14, 2), substr($d, 17, 2), substr($d, 5, 2), substr($d, 8, 2), substr($d, 0, 4));
	}

	private function temporary_function($arr)
	{
		arr($this->make_master_import_happy($arr));
		exit;
		/*
		Yritus (event) => 1
		Asukoht (location) => 2
		Artist (artist) => 3
		Huvitegevus (hobby) => 4
		 (promotor) => 5
		*/
		$url = "https://w2.dev.delfi.ee/";
//		$url = "http://won2q.delfi.ee/";
//		$url .= "search.php?lang=est&partner=delfi&action=GetAttributes";
//		$url .= "search.php?lang=est&partner=delfi&action=GetObjectTypes";
//		$url .= "search.php?lang=est&partner=delfi&action=RunSearchQueryFaceted&object_types%5b%5d=2&limit=10";
//		$url .= "search.php?lang=est&partner=delfi&action=RunSearchQuery&object_types%5b%5d=2&limit=10000";
//		$url .= "search.php?lang=est&partner=delfi&action=GetAttributeValues&attr_id=161";
		$url .= "search.php?lang=est&partner=delfi&action=GetObject&object_id=16323";
//		$url .= "search.php?lang=est&partner=delfi&action=GetObjectsModifiedAfter&modified_after=2008-04-08";
//		$url .= "search.php?lang=est&partner=delfi&action=RunSearchQueryFaceted&object_types%5b%5d=1&limit=1000&display_attributes%5b%5d=120&display_attributes%5b%5d=161";
//		$url .= "search.php?action=getobjecttop&top_limit=1&partner=delfi&lang=est";
		$input = file_get_contents($url);
		arr($url);
		arr(json_decode($input));
		exit;
	}
}

?>
