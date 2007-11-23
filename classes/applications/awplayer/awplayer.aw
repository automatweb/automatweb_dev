<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/awplayer/Attic/awplayer.aw,v 1.5 2007/11/23 14:25:12 kristo Exp $
// awplayer.aw - AW Pleier 
/*

@classinfo syslog_type=ST_AWPLAYER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 mantainer=hannes

@default table=objects
@default group=general

@property control type=text field=meta method=serialize
@caption Kontroll

@property search type=textbox field=meta method=serialize
@caption Otsing

@property all_songs_table type=table store=no no_caption=1

@groupinfo settings caption=Seaded
@default group=settings

@property name type=textbox
@caption Nimi


*/

class awplayer extends class_base
{
	function awplayer()
	{
		$this->init(array(
			"tpldir" => "applications/awplayer/awplayer",
			"clid" => CL_AWPLAYER
		));
	}
	
	function get_cover_url($s_keywords)
	{
		$s_google_search = "http://images.google.com/images?svnum=100&um=1&hl=et&lr=&rls=en&q=".urlencode($s_keywords)	;
		$s_result = file_get_contents($s_google_search);
		if (preg_match ( "/dyn.Img\(\".*\".*\".*\".*\".*\".*\"(.*)\".*\)/imsU", $s_result, $a_matches ))
		{
			return $a_matches[1]; // first image
		}
	}
	
	function get_play_url($id,$name)
	{
		$retval = str_replace("automatweb/","",$this->mk_my_orb("play", array("id" => $id),"awplayer", false,true,"/"));
		return $retval;
	}
	
	function _get_control($arr)
	{
		$o = & $arr["obj_inst"];
		
		if(strlen($o->name())>0)
		{
			$s_link = html::href(array(
				"url" => "JavaScript: void(0)",
				"caption" => "mängi",
				"onclick" => 'myRef = window.open("'.$this->get_play_url($o->id(), $s_filename).'","AW MP3 Mängija","left="+((screen.width/2)-(250/2))+",top="+screen.height/5+",width=250,height=450,toolbar=0,resizable=0,location=0,directories=0,status=0,menubar=0,scrollbars=0")',
			));
			
			$arr["prop"]["value"] = $s_link;
		}
	}
	
	function _get_all_songs_table($arr)
	{
		$table =& $arr["prop"]["vcl_inst"];
		$o_player = & $arr["obj_inst"];
		
		$s_search = trim($o_player->prop("search"));
		$ol = $this->get_playlist($s_search);
		
		$table->define_field(array(
			"name" => "title",
			"caption" => t("Loo nimi"),
			"sortable" => 1,
			"align" => "left",
		));
		
		$table->define_field(array(
			"name" => "time",
			"caption" => t("Kestvus"),
			"sortable" => 1,
			"align" => "left",
		));
		
		$table->define_field(array(
			"name" => "artist",
			"caption" => t("Artist"),
			"sortable" => 1,
			"align" => "left"
		));
		
		$table->define_field(array(
			"name" => "album",
			"caption" => t("Album"),
			"sortable" => 1,
			"align" => "left"
		));
		
		$table->define_field(array(
			"name" => "genre",
			"caption" => t("Zanr"),
			"sortable" => 1,
			"align" => "left"
		));
		
		$table->define_field(array(
			"name" => "rate",
			"caption" => t("Rate"),
			"sortable" => 1,
			"align" => "left"
		));
		
		$table->define_field(array(
			"name" => "play_count",
			"caption" => t("Mängitud"),
			"sortable" => 1,
			"align" => "left"
		));
		
		$table->define_field(array(
			"name" => "play",
			"caption" => t("Mängi"),
			"sortable" => 1,
			"align" => "left"
		));
		
		for ($o = $ol->begin(); !$ol->end(); $o =& $ol->next())
        {
			classload("applications/awplayer/mp3");
			$s_link = mp3::get_play_url($o->id(),$o->name());
		
			$table->define_data(array(
				"title" => html::href(array(
					"url" => "JavaScript: void(0)",
					"caption" => $o->prop("title"),
					"onclick" => 'myRef = window.open("'.$s_link.'","AW MP3 Mängija","left="+((screen.width/2)-(350/2))+",top="+screen.height/5+",width=350,height=150,toolbar=0,resizable=0,location=0,directories=0,status=0,menubar=0,scrollbars=0")',
				)),
				"time" => $o->prop("mpeg_info_playtime_string"),
				 "artist" =>  $o->prop("artist"),
				 "album" =>  $o->prop("album"),
 				 "genre" =>  $o->prop("genre"),
				 "play_count" => $o->prop("play_count"),
				"play" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id()),"mp3", false,true),
					"caption" => "info",
				)),
			));
        }
		
		$table->define_pageselector(array(
				"type"=>"lb",
				"records_per_page"=>100,
				"position"=>"both",
		));
	}
	
	/** Playis mp3's that are listed in aw player
		
		@attrib name=play params=name nologin="1" default="0" is_public="1"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function play($arr)
	{
		classload("applications/awplayer/mp3");
		$o = new object($arr["id"]);
		
		$this->read_template("awplayer.tpl");
			$this->vars(array(
			"awplayer_oid" => $o->id(),
			"file_name" => mp3::normalize_name($o->name()),
		));
		
		echo $this->parse();
		
		die();
	}
	
	function get_playlist($s_search)
	{
		if (strlen($s_search)>0)
		{
			$a_search_keys = explode( " ", $s_search);
			
			for($i=0;$i<count($a_search_keys);$i++)
			{
				$a_search_keys[$i] = "%". $a_search_keys[$i] . "%";
			}
			
			$filt = array(
			"class_id" => CL_MP3,
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"title" => $a_search_keys,
					"album" => $a_search_keys,
					"artist" => $a_search_keys,
					"genre" => $a_search_keys,
					"year" => $a_search_keys,
					)
				))
			);
		}
		else
		{
			$filt = array(
				"class_id" => CL_MP3,
				"lang_id" => array(),
			);
		}
		
		$ol = new object_list($filt);
		return $ol;
	}
	
	/** Loob playlisti
		
		@attrib name=playlist params=name nologin="1" default="0" is_public="1"
		
		@param id required
		
		@returns
		
		@comment
	**/
	function playlist($arr)
	{
		classload("applications/awplayer/mp3");
		$o = new object($arr["id"]);
		
		$s_search = trim($o->prop("search"));
		$ol = $this->get_playlist($s_search);
		
		$this->read_template("playlist.tpl");
		$this->submerge=1;

		$tmp='';
		for ($o = $ol->begin(); !$ol->end(); $o =& $ol->next())
        {
			$this->vars(array(
				"id"=> $o->id(),
				"title"=> $o->prop("title"),
				"artist" => $o->prop("artist"),
				"url_mp3" => mp3::get_download_url($o->id(),"fail.mp3"),
				"url_info" => mp3::get_lasering_url($o->prop("album")),
				"url_image" => $this->get_cover_url($o->prop("artist")." ".$o->prop("album")),
			));
			$tmp.= $this->parse("TRACK");
		}
		
		$this->vars(array(
				"TRACK" => $tmp,
		));
		
		die(utf8_encode($this->parse()));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
