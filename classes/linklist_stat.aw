<?php
class linklist_stat extends aw_template
{
	function linklist_stat()
	{
		$this->init("linklist");
	}

	function change($arr)
	{
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
		$predefine=array(
			"oid"=>"külastatuimad saidid",
			"uid"=>"tihedamad kasutajad", 
			"dayofmonth"=>"kuupäevade järgi",
			"hour"=>"tundide kaupa",
			"year"=>"aasta lõikes",
			"minute"=>"minutite järgi",
			"weekday"=>"nädalapäevade kaupa"
		);

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
		
		
		
		
alates:{VAR:from_date}<br />
kuni{VAR:till_date}<br />
<input type="submit" value="otsi">

<input checked type='checkbox' name='from' VALUE='1'></td>


<!-- SUB: predefine -->
<li><a href="{VAR:link}">{VAR:str}</a></li>
<!-- END SUB: predefine -->

<tr>
<td>
{VAR:kuu_date}
</td>
<td>
<input type=submit name=kuu_stat value="kuu statistika">
</td>
</tr>
<tr>
<td>
{VAR:aasta_date}
</td>
<td>
<input type=submit name=aasta_stat value="aasta statistika">
</td>
</tr>



<tr>
<td class="celltext" colspan=2>

{VAR:abix}
<fieldset>

<legend>linikogu statistika</legend>
select count from linikogu_stat: {VAR:caunt}<br />
select count from linikogu_stat where action=1(brausitud): {VAR:caunt_dirs} <br />
select count from linikogu_stat  where action=2(linke vaadatud): {VAR:caunt_links} <br />
select count from linikogu_stat where oid=666:_ {VAR:caunt_linke} <br />
</fieldset>


{VAR:stat_out}

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

			$t->parse_xml_def($this->cfg['basedir'].'/xml/generic_table.xml');

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