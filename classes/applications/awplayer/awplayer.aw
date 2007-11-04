<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/awplayer/Attic/awplayer.aw,v 1.3 2007/11/04 20:30:24 hannes Exp $
// awplayer.aw - AW Pleier 
/*

@classinfo syslog_type=ST_AWPLAYER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

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
	
	/** N&auml;itab mp3'e. DUH. 
		
		@attrib name=play params=name nologin="1" default="0" is_public="1"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function play($arr)
	{
		$o = new object($arr["id"]);
		
		$s_html = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		   "http://www.w3.org/TR/html4/loose.dtd">
		
		<html>
		<head>
			<title>AW MP3 Mängija</title>
			<script type="text/javascript" src="'.aw_ini_get("baseurl").'/automatweb/js/jw_mp3_player/swfobject.js"></script>
			
			<script type="text/javascript">
			
			<script type="text/javascript">
				// some variables to save
				var currentPosition;
				var currentVolume;
				var currentItem;
			
				// these functions are caught by the JavascriptView object of the player.
				function sendEvent(typ,prm) { thisMovie("mpl").sendEvent(typ,prm); };
				function getUpdate(typ,pr1,pr2,pid) {
					if(typ == "time") { currentPosition = pr1; }
					else if(typ == "volume") { currentVolume = pr1; }
					else if(typ == "item") { currentItem = pr1; setTimeout("getItemData(currentItem)",100); }
					var id = document.getElementById(typ);
					id.innerHTML = typ+ ": "+Math.round(pr1);
					pr2 == undefined ? null: id.innerHTML += ", "+Math.round(pr2);
					if(pid != "null") {
						document.getElementById("pid").innerHTML = "(received from the player with id <i>"+pid+"</i>)";
					}
				};
			
				// These functions are caught by the feeder object of the player.
				function loadFile(obj) { thisMovie("mpl").loadFile(obj); };
				function addItem(obj,idx) { thisMovie("mpl").addItem(obj,idx); }
				function removeItem(idx) { thisMovie("mpl").removeItem(idx); }
				function getItemData(idx) {
					var obj = thisMovie("mpl").itemData(idx);
					var nodes = "";
					for(var i in obj) { 
						nodes += "<li>"+i+": "+obj[i]+"</li>"; 
					}
					document.getElementById("data").innerHTML = nodes;
				};
			
				// This is a javascript handler for the player and is always needed.
				function thisMovie(movieName) {
				    if(navigator.appName.indexOf("Microsoft") != -1) {
						return window[movieName];
					} else {
						return document[movieName];
					}
				};
			
			</script>
			
			
			</script>
			
			<style>
			body, p {margin: 0;padding: 0;}
			</style>
		</head>
		<body>
		
		<p id="player2"><a href="http://www.macromedia.com/go/getflashplayer">Sikuta</a> flash pleier.</p>
		<script type="text/javascript">
			var s2 = new SWFObject("'.aw_ini_get("baseurl").'/automatweb/js/jw_mp3_player/mp3player.swf", "mpl", "250", "450", "7");
			s2.addVariable("file","'.aw_ini_get("baseurl").'/orb.aw/class=awplayer/action=playlist/id=404776/playlist.xml");
			s2.addVariable("title","'.$o->name().'");
			s2.addVariable("autostart", true);
			s2.addVariable("overstretch", "fit"); // scretch image
			s2.addVariable("repeat", "list");
			s2.addVariable("autoscroll", false);
			s2.addVariable("shownavigation", true);
			s2.addVariable("enablejs", true);
				s2.addVariable("javascriptid","mpl");
			// for some strange reason this does not work when i use ? after orb.aw and & marks. only / works
			s2.addVariable("callback", "'.aw_ini_get("baseurl").'/orb.aw/class=mp3/action=log_play_statistics/id=404776");
			//s2.addVariable("callback", "http://hannes.dev.struktuur.ee/vv_files/statistics.php");
			//s2.addVariable("displaywidth", 150);
			//s2.addVariable("showeq", true);
			s2.addVariable("backcolor","0x00000");
			s2.addVariable("frontcolor","0xEECCDD");
			s2.addVariable("lightcolor","0xCC0066");
			s2.addVariable("displayheight","200");
			s2.addVariable("width","250");
			s2.addVariable("height","450");
			s2.write("player2");
		</script>
		
		
		
		</body>
		</html>
		';
	echo $s_html;
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
	
	/** N&auml;itab mp3'e. DUH. 
		
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
		
		$s_out = "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
		$s_out .= "	<title>AW Pleieri playlist</title>\n";
		$s_out .= "	<info>http://www.jeroenwijering.com/</info>\n";
		$s_out .= "	<trackList>\n";
		
		// .. then we loop through the mysql array ..
		for ($o = $ol->begin(); !$ol->end(); $o =& $ol->next())
        {
			$s_out .= "		<track>\n";
			$s_out .= "			<title>".utf8_encode($o->prop("title"))."</title>\n";
			$s_out .= "			<creator>".utf8_encode($o->prop("artist"))."</creator>\n";
			$s_out .= "			<location>".mp3::get_download_url($o->id(),"fail.mp3")."</location>\n";
			$s_out .= "			<info>".mp3::get_lasering_url($o->prop("album"))."</info>\n";
			$s_out .= "			<image>".$this->get_cover_url($o->prop("artist")." ".$o->prop("album"))."</image>\n";
			$s_out .= "			<identifier>".$o->id()."</identifier>";
			$s_out .= "		</track>\n";
		}
		 
		// .. and last we add the closing tags
		$s_out .= "	</trackList>\n";
		$s_out .= "</playlist>\n";
		header("Accept-Ranges: bytes");
		header("Content-Length: ".strlen($s_out));
		header("content-type:text/xml;charset=utf-8");
		header("Cache-control: public");
		header("Content-Disposition: inline; filename=\"playlist.xml\"");
		//header("Content-Length: ".strlen($fc["content"]));
		//header("Pragma: no-cache");
		die($s_out);
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
