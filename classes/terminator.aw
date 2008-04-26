<?php
// $Header: /home/cvs/automatweb_dev/classes/terminator.aw,v 1.2 2008/04/26 16:21:11 kristo Exp $
// terminator.aw - The Terminator 
/*

@classinfo syslog_type=ST_TERMINATOR relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property pers type=relpicker reltype=RELTYPE_PERSON store=connect delete_button=1 delete_rels_button=1
@caption Isik

@reltype PERSON value=1 clid=CL_CRM_PERSON
@caption Isik

*/

class terminator extends class_base
{
	function terminator()
	{
		$this->init(array(
			"tpldir" => "terminator",
			"clid" => CL_TERMINATOR
		));
	}

	function get_property($arr)
	{
		$o = obj();
		$o->set_class_id(CL_CRM_PERSON);
		$o->set_parent($arr["obj_inst"]->id());
		$o->set_prop("rank", 40753);
		$o->save();
		exit;

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
	function milliaeg(){ // tekitame funktsiooni, mis väljastab hetke aja
		list($usec, $sec) = explode(" ", microtime()); // leiame hetkeaja mikrosekundites
		return ((float)$usec+(float)$sec); // väljastame funktsioonist praeguse aja
	}

	function juhuslik_parool($pikkus) {
		$uus_parool = "";
		$rida = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

		mt_srand((double)microtime()*1000000);

		for ($i=1; $i <= $pikkus; $i++) {
			$uus_parool .= substr($rida, mt_rand(0,strlen($rida)-1), 1);
		}
		
		return $uus_parool;
	} 

	function init_arr()
	{
		$r = array();
		for($i = 0; $i < 10000; $i++)
		{
			$r[$this->juhuslik_parool(10)] = $this->juhuslik_parool(100);
		}
		return $r;
	}

	function test_speed()
	{
		$arr = $this->init_arr();

		print "aw_serialize()<br>";
		$leheAlgusAeg = $this->milliaeg(); // määrame lehe laadimise algusaja (hetkeaeg) 
		for($i = 0; $i < 15; $i++)
		{
			$str = aw_serialize($arr);
		}
		$leheLoppAeg = $this->milliaeg(); // määrame lehe laadimise lõpuaja (hetkeaeg)
		$leheKulunudAeg = $leheLoppAeg-$leheAlgusAeg; // arvutame lehe laadimisele kulunud aja
		$t = number_format($leheKulunudAeg, 4); // kuna aega väljendatakse väga pikalt, teeme selle lühemale kujule (4 kohta peale koma)
		print "Aega kulus: ".$t."<br>";
		print "Stringi suurus: ".strlen($str)."<br><br>";

		print "aw_unserialize()<br>";
		$leheAlgusAeg = $this->milliaeg(); // määrame lehe laadimise algusaja (hetkeaeg) 
		for($i = 0; $i < 15; $i++)
		{
			$arr2 = aw_unserialize($str);
		}
		$leheLoppAeg = $this->milliaeg(); // määrame lehe laadimise lõpuaja (hetkeaeg)
		$leheKulunudAeg = $leheLoppAeg-$leheAlgusAeg; // arvutame lehe laadimisele kulunud aja
		$t = number_format($leheKulunudAeg, 4); // kuna aega väljendatakse väga pikalt, teeme selle lühemale kujule (4 kohta peale koma)
		print "Aega kulus: ".$t."<br><br>";
		
		print "json_encode()<br>";
		$leheAlgusAeg = $this->milliaeg(); // määrame lehe laadimise algusaja (hetkeaeg) 
		for($i = 0; $i < 15; $i++)
		{
			$str = json_encode($arr);
		}
		$leheLoppAeg = $this->milliaeg(); // määrame lehe laadimise lõpuaja (hetkeaeg)
		$leheKulunudAeg = $leheLoppAeg-$leheAlgusAeg; // arvutame lehe laadimisele kulunud aja
		$t = number_format($leheKulunudAeg, 4); // kuna aega väljendatakse väga pikalt, teeme selle lühemale kujule (4 kohta peale koma)
		print "Aega kulus: ".$t."<br>";
		print "Stringi suurus: ".strlen($str)."<br><br>";
		
		print "json_decode()<br>";
		$leheAlgusAeg = $this->milliaeg(); // määrame lehe laadimise algusaja (hetkeaeg) 
		for($i = 0; $i < 15; $i++)
		{
			$arr2 = json_decode($str);
		}
		$leheLoppAeg = $this->milliaeg(); // määrame lehe laadimise lõpuaja (hetkeaeg)
		$leheKulunudAeg = $leheLoppAeg-$leheAlgusAeg; // arvutame lehe laadimisele kulunud aja
		$t = number_format($leheKulunudAeg, 4); // kuna aega väljendatakse väga pikalt, teeme selle lühemale kujule (4 kohta peale koma)
		print "Aega kulus: ".$t."<br>";
	}
}
?>
