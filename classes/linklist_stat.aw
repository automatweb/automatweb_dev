<?php
global $orb_defs;
$orb_defs["linkslist"] = "xml";
class linklist_stat extends aw_template
{

	function linklist_stat()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("linklist");
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("stats.tpl");
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ligikogu stati");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda lingikogu stati");
		}
		$ob = $this->get_object($id); //NB see on siin lingikogu object mitte lingikogu_stat



		if (!$sid)//add stat object
		{
			$sid = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_LINK_LIST_STAT,
				"comment" => $comment,
				"metadata" => array()
			));

			$this->set_object_metadata(array(
				"oid" => $id,
				"key" => "sid",
				"value" => $sid,
			));
		}

		$st = $this->get_object($sid); //see on õige lingikogu_stat

//		$from_date=$st["meta"]["from_date"];
		load_vcl("date_edit");
		$from=date_edit::get_timestamp($st["meta"]["from_date"]);
		$till=date_edit::get_timestamp($st["meta"]["till_date"]);
		$kuu=date_edit::get_timestamp($st["meta"]["kuu_date"]);
		$aasta=date_edit::get_timestamp($st["meta"]["aasta_date"]);

		$limit= "limit 30";
		$now=time();
$predefine=array("oid"=>"külastatuimad saidid","uid"=>"tihedamad kasutajad", "dayofmonth"=>"kuupäevade järgi","hour"=>"tundide kaupa","year"=>"aasta lõikes","minute"=>"minutite järgi","weekday"=>"nädalapäevade kaupa");

// tahame ikka vist konreetse lingikogu kohta statistikat onju
$where="where lkid=".$id;

		switch($stat) 
		{
			case "dayofmonth": 
				$q="select * from lingikogu_stat $where GROUP BY DAYOFMONTH(FROM_UNIXTIME(tm))";
				$xml="";
			break;
			case "hour":
				$q="select * from lingikogu_stat $where GROUP BY HOUR(FROM_UNIXTIME(tm))";
			break;
			case "year":
				$q="select * from lingikogu_stat $where GROUP BY YEAR(FROM_UNIXTIME(tm))";			
			break;
			case "minute":
				$q="select * from lingikogu_stat $where GROUP BY MINUTE(FROM_UNIXTIME(tm))";			
			break;
			case "weekday":
//				$q="select * from lingikogu_stat $where GROUP BY DAYOFWEEK(FROM_UNIXTIME(tm))";
				$q="select * from lingikogu_stat $where GROUP BY WEEKDAY(FROM_UNIXTIME(tm))";			
			break;
			case "oid": 
				$q="select * from lingikogu_stat $where GROUP BY oid";						
			break;
			case "uid": 
				$q="select * from lingikogu_stat $where GROUP BY uid";						
			break;

			default:

				$q="select * from lingikogu_stat $where and tm between $from and $till";						
		}

echo $q;
		//siin tööötleme päringust saadud andmed tabelisse
		if ($q)
		$stat_out = $this->show_stats($id,$q);


		$from_date = new date_edit("from_date",$from);
		$from_date->configure(array(
			"month" => "",
			"day" => "",
			"year" => "",
			"hour" => "",
			"minute" => "",
		));

		$till_date = new date_edit("till_date",$till);
		$till_date->configure(array(
			"month" => "",
			"day" => "",
			"year" => "",
			"hour" => "",
			"minute" => "",
		));

		$kuu_date = new date_edit("kuu_date",$kuu);
		$kuu_date->configure(array(
			"month" => "",
		));

		$aasta_date = new date_edit("aasta_date",$aasta);
		$aasta_date->configure(array(
			"year" => "",
		));



//echo date('Y/m/d &\n\b\s\p\; H:i');


		classload("linklist"); //vaja oleks see toolbar kätte saada

		foreach($predefine as $key => $val)
		{
			$this->vars(array(
				"link" => $this->mk_my_orb("change", array("stat"=>$key, "id" => $id, "return_url" => urlencode($return_url))),
				"str" => $val,
			));
			$predefined.=$this->parse("predefine");
		}


		$this->vars(array(
			"caunt"=>$this->db_fetch_field("select count(id) as caunt from lingikogu_stat","caunt"),
			"caunt_dirs"=>$this->db_fetch_field("select count(id) as caunt from lingikogu_stat where action=1","caunt"),
			"caunt_links"=>$this->db_fetch_field("select count(id) as caunt from lingikogu_stat where action=2","caunt"),

			"stat_out"=>$stat_out,
			"from_date" => $from_date->gen_edit_form("from_date",$from),
			"till_date" => $till_date->gen_edit_form("till_date",$till),
			"kuu_date" => $kuu_date->gen_edit_form("kuu_date",0),
			"aasta_date" => $aasta_date->gen_edit_form("aasta_date",0),


			"toolbar" => linklist::lingikogu_toolbar(array("id"=>$id,"sid"=>$sid,"ob"=>"linklist")),
			"abix" => $abix,
			"name" => $ob["name"],


			"predefine" => $predefined,

			"reforb" => $this->mk_reforb("submit", array("parent"=>$parent, "id" => $id, "sid" => $sid, "return_url" => urlencode($return_url))),
		));
		return $this->parse();	
	
	
	}


	function submit($arr)
	{
		extract($arr);
		if ($sid)
		{
			$this->upd_object(array(
				"oid" => $sid,
				"name" => "lk stat",
				"comment" => "lkogu stat on see",
				"metadata" => array(
					"from_date" => $from_date,
					"till_date" => $till_date,
					"kuu_date" => $kuu_date,
					"aasta_date" => $aasta_date,
				)
			));
		}
		else
		{
			die("NB sid puudu");
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id,"sid" => $sid, "return_url" => urlencode($return_url)));
	}

/////////////////////////////////

	
	

	
	function show_stats($id,$q,$xml="show_stats.xml") //id,query,
	{
	
	$this->db_query($q);

			load_vcl("table");
			$t = new aw_table(array(
				"prefix" => "lingikogu_stats", 
			));
//echo $this->cfg["site_basedir"];
//			$t->parse_xml_def($this->cfg["site_basedir"]."/xml/linklist/show_stats.xml"); 
			$t->parse_xml_def("/www/automatweb_dev/xml/linklist/".$xml); 
			
			while ($row= $this->db_next()) 
			{ 
				$tm=date('Y/m/d&\n\b\s\p\;H:i', $row["tm"]);
				$t->define_data(array(
					"id"=>$row["id"],
					"lkid"=>$row["lkid"],
					"oid"=>$row["oid"],				
					"uid"=>$row["uid"],
					"action"=>$row["action"],
					"tm"=>$tm,
				)); 
			} 
			$t->sort_by(); 
			return $t->draw();
}


}
















/*
if (($from) && ($till))
{
$q = "select * from lingikogu_stat where tm between $from and $till $limit";
}
elseif($from)
{
$q = "select * from lingikogu_stat where tm>=$from $limit";
}
elseif($till)
{
$q = "select * from lingikogu_stat where tm<=$till $limit";
}
else
$q = "select * from lingikogu_stat $limit";

/*
$q = "select * from lingikogu_stat where tm>='$tm' limit 50";
$q = "select * from lingikogu_stat where between '$t1' and '$t2'";
*/
?>