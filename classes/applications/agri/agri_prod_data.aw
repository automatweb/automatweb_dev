<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/agri/Attic/agri_prod_data.aw,v 1.1 2004/03/28 21:45:14 kristo Exp $
// agri_prod_data.aw - Agri toote andmed 
/*
@tableinfo agri_prod_data index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_AGRI_PROD_DATA no_comment=1 no_status=1

@groupinfo general caption="Kogused"

@default table=agri_prod_data
@default group=general

@property prod_code type=textbox table=agri_prod_data group=general,locs,special,owners
@caption Tootekood

@property data_tbl type=table no_caption=1


///////////// asukohad group
@groupinfo locs caption="Asukohad"
@default group=locs

@property prods_locs type=table no_caption=1


///////////// specials group
@groupinfo special caption="Erinevused ja seletused"
@default group=special

@property special_left type=table 
@caption J&auml;&auml;gid

@property over_left_txt
@caption &Uuml;leliigsed varud

@property sp_avg_left type=textbox size=10 
@caption Varud 30.04.2004 seisuga (kell 24:00) -  keskmine j&auml;&auml;k x 1,1 = 

@property sp_avg_com type=textbox size=10
@caption Sellest ettev&otilde;ttes hoiul olev

@property over_left_txt2
@caption Varude suurenemise p&otilde;hjused

@property over_left type=table 
@caption &Uuml;leliigsed varud

@property special_expl type=textarea rows=10 cols=60
@caption Selgitused

///////////// owners group
@groupinfo owners caption="Omanikud"
@default group=owners

@property prods_owners type=table no_caption=1

@property prods_locs_txt type=text no_caption=1 group=locs,owners store=no


///////////// data accepted group
@groupinfo data_accepted caption="Andmed sisestatud" submit=no

@property data_accepted group=data_accepted type=text no_caption=1 store=no

*/

class agri_prod_data extends class_base
{
	var $entry_lut = array(
		1 => "J&auml;&auml;k perioodi alguses",
		2 => "Ostetud tooraine v&otilde;i toode",
		3 => "...Eestist",
		4 => "...Import",
		5 => "Toodang",
		6 => "Kasutatud &uuml;mbert&ouml;&ouml;tlemisel",
		7 => "M&uuml;&uuml;k",
		8 => "...Eestis",
		9 => "...Eksport",
		10 => "J&auml;&auml;k perioodi l&otilde;pus"
	);

	var $mon_lut = array(
		0 => array("m" => 5, "y" => 0, "n" => "Mai"),
		1 => array("m" => 6, "y" => 0, "n" => "Juuni"),
		2 => array("m" => 7, "y" => 0, "n" => "Juuli"),
		3 => array("m" => 8, "y" => 0, "n" => "Aug"),
		4 => array("m" => 9, "y" => 0, "n" => "Sept"),
		5 => array("m" => 10, "y" => 0, "n" => "Okt"),
		6 => array("m" => 11, "y" => 0, "n" => "Nov"),
		7 => array("m" => 12, "y" => 0, "n" => "Dets"),
		8 => array("m" => 1, "y" => 1, "n" => "Jaan"),
		9 => array("m" => 2, "y" => 1, "n" => "Veebr"),
		10 => array("m" => 3, "y" => 1, "n" => "M&auml;rts"),
		11 => array("m" => 4, "y" => 1, "n" => "Apr")
	);

	var	$rs_lt = array(
		"1. Tootmis- ja t&ouml;&ouml;tlemismahu suurenemine:",
		"2. Kaubandusmahu suurenemine",
		"3. Ekspordi v&auml;henemine tootjast v&otilde;i t&ouml;&oumltlejast mitteoleneval P&otilde;hjusel",
		"4. P&otilde;llumajandustoote l&otilde;ppvalmimist&auml;htaeg",
		"5. Toote nomenklatuuri muutus",
		"6. Laovarud on tekkinud enne 2003.a. III kvartalit"
	);


	function agri_prod_data()
	{
		$this->init(array(
			"tpldir" => "applications/agri/agri_prod_data",
			"clid" => CL_AGRI_PROD_DATA
		));
	}

	function get_property($arr)
	{
		$this->_init($arr["obj_inst"]);

		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "name":
				return PROP_IGNORE;
				break;
			
			case "data_tbl":
				$this->do_data_tbl($arr);
				break;

			case "data_accepted":
				$c = reset($arr["obj_inst"]->connections_to(array("type" => 1 /* RELTYPE_PROD_DATA */)));
				$md = $c->from();
				$prop["value"] = "Andmed on edukalt vastu v&otilde;etud, <br><br> <input class='aw04formbutton' type='button' onClick='window.location=\"".$this->mk_my_orb("change", array("id" => $md->id(), "group" => "adds"), $md->class_id())."\"' value='j&auml;rgmise toote sisestamiseks vajutage siia'>";
				break;

			case "prods_locs":
				$this->do_prods_locs_tbl($arr);
				break;

			case "prods_owners":
				$this->do_prods_owners_tbl($arr);
				break;

			case "prods_locs_txt":
				$prop["value"] = "* Sisaldab j&auml;rgmisi andmeid:<br>Maakond<br>Vald/linn<br>K&uuml;la/alev/alevik<br>T&auml;nav/maja<br>Postiindeks<br>";
				break;

			case "special_left":
				$this->do_special_left_tbl($arr);
				break;

			case "over_left":
				$this->do_over_left_tbl($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$this->_init($arr["obj_inst"]);
		
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "prod_code":
				// get the name for code
				$ol = new object_list(array(
					"class_id" => CL_AGRI_PROD_CODE,
					"code" => $prop["value"]
				));
				if ($ol->count() < 1)
				{
					$prop["error"] = "Sellist tootekoodi pole olemas!";
					return PROP_FATAL_ERROR;
				}
				$o = $ol->begin();
				$arr["obj_inst"]->set_name($o->name());
				break;

			case "data_tbl":
				if ($this->prod_code->prop("period") == 1)
				{
					$this->save_long_data_array($arr["obj_inst"]->id(), $arr["request"]["data"]);
				}
				else
				{
					$this->save_short_data_array($arr["obj_inst"]->id(), $arr["request"]["data"]);
				}
				break;

			case "prods_locs":
				$this->save_prods_locs_tbl($arr);
				break;

			case "prods_owners":
				$this->save_prods_owners_tbl($arr);
				break;

			case "special_left":
				$this->save_special_left_tbl($arr);
				break;

			case "over_left":
				$this->save_over_left_tbl($arr);
				break;
		}
		return $retval;
	}	

	function init_data_tbl_long(&$t)
	{
		$t->define_field(array(
			"name" => "desc",
		));
		$t->define_field(array(
			"name" => "m1",
		));
		$t->define_field(array(
			"name" => "m2",
		));
		$t->define_field(array(
			"name" => "m3",
		));
		$t->define_field(array(
			"name" => "m4",
		));
		$t->define_field(array(
			"name" => "m5",
		));
		$t->define_field(array(
			"name" => "m6",
		));
		$t->define_field(array(
			"name" => "m7",
		));
		$t->define_field(array(
			"name" => "m8",
		));
		$t->define_field(array(
			"name" => "m9",
		));
		$t->define_field(array(
			"name" => "m10",
		));
		$t->define_field(array(
			"name" => "m11",
		));
		$t->define_field(array(
			"name" => "m12",
		));
		$t->define_field(array(
			"name" => "total",
		));
	}

	function init_data_tbl_short(&$t)
	{
		$t->define_field(array(
			"name" => "desc",
		));
		$t->define_field(array(
			"name" => "total",
		));
		$t->define_field(array(
			"name" => "total_eek",
		));
	}

	function do_data_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->set_sortable(false);

		if ($this->prod_code->prop("period") == 1)
		{
			$this->init_data_tbl_long($t);
			$data = $this->get_long_data_array($arr["obj_inst"]->id());

			for($y = 0; $y < 5; $y++)
			{
				// add title
				$td = array();
				for ($mon = 0; $mon < 12; $mon++)
				{
					$ry = ($y+$this->mon_lut[$mon]["y"]);
					if ($y == 4)
					{
						$ry--;
					}
					$td["m".($mon+1)] = $this->mon_lut[$mon]["n"].".0".$ry;
				}
				$td["total"] = "KOKKU";
				if ($y == 4)
				{
					$td["desc"] = "M&otilde;&otilde;t&uuml;hik: <b>krooni</b>";
				}
				else
				{
					$td["desc"] = "M&otilde;&otilde;t&uuml;hik: <b>kilogramm,liiter</b>";
				}
				$t->define_data($td);

				// now entry rows
				foreach($this->entry_lut as $nr => $txt)
				{
					$td = array(
						"desc" => $txt,
					);
					$sum = 0;
					for ($mon = 0; $mon < 12; $mon++)
					{
						$rm = $this->mon_lut[$mon]["m"];
						$ry = $y + $this->mon_lut[$mon]["y"];
						$dn = "data[kg][$ry][$rm][$nr]";
						$vl = $data["kg"][$ry][$rm][$nr];
						if ($y == 4)
						{
							$ry--;
							$dn = "data[eek][$ry][$rm][$nr]";
							$vl = $data["eek"][$ry][$rm][$nr];
						}

						$td["m".($mon+1)] = html::textbox(array(
							"name" => $dn,
							"value" => $vl,
							"size" => "5"
						));
						$sum += $vl;
					}
					$td["total"] = $sum;
					$t->define_data($td);
				}
				$t->define_data(array());
			}
		}
		else
		{
			$this->init_data_tbl_short($t);
			$data = $this->get_short_data_array($arr["obj_inst"]->id());
			for($y = 0; $y < 4; $y++)
			{
				$td = array(
					"desc" => "<b>Vaadeldav periood</b>",
					"total" => "<b>01.05.200".$y." - 30.04.200".($y+1)."</b>"
				);
				$t->define_data($td);
				$td = array(
					"desc" => "&nbsp;",
					"total" => "Kogus, kg"
				);
				if ($y == 3)
				{
					$td["total_eek"] = "V&auml;&auml;rtus, EEK";
				}
				$t->define_data($td);
				foreach($this->entry_lut as $nr => $txt)
				{
					$td = array(
						"desc" => $txt,
						"total" => html::textbox(array(
							"name" => "data[kg][$y][$nr]",
							"value" => $data["kg"][$y][$nr],
							"size" => "5"
						))
					);
					if ($y == 3)
					{
						$td["total_eek"] = html::textbox(array(
							"name" => "data[eek][$y][$nr]",
							"value" => $data["eek"][$y][$nr],
							"size" => "5"
						));
					}
					$t->define_data($td);
				}
				$t->define_data(array());
			}
		}
	}

	function get_long_data_array($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM agri_prod_data_rows WHERE obj_id = '$id'");
		while ($row = $this->db_next())
		{
			$ret[$row["unit"]][$row["year"]][$row["mon"]][$row["nr"]] = $row["value"];
		}

		return $ret;
	}

	function save_long_data_array($id, $dat)
	{
		$this->db_query("DELETE FROM agri_prod_data_rows WHERE obj_id = '$id'");
		$sums = array();
		foreach($dat as $unit => $udata)
		{
			foreach($udata as $year => $ydata)
			{
				foreach($ydata as $mon => $mdata)
				{
					foreach($mdata as $nr => $vl)
					{
						$sums[$unit][$year][$nr] += $vl;
						$this->db_query("INSERT INTO agri_prod_data_rows(unit,year,mon,nr,value,obj_id)
							VALUES('$unit','$year','$mon','$nr','$vl','$id')");
					}
				}
			}
		}

		$this->db_query("DELETE FROM agri_prod_data_row_sums WHERE obj_id = '$id'");
		foreach($sums as $unit => $udata)
		{
			foreach($udata as $year => $ydata)
			{
				foreach($ydata as $nr => $vl)
				{
					$this->db_query("INSERT INTO agri_prod_data_row_sums(unit,year,nr,value,obj_id)
						VALUES('$unit','$year','$nr','$vl','$id')");
				}
			}
		}
	}

	function _init($o)
	{
		if (!$this->inited)
		{
			$ol = new object_list(array(
				"class_id" => CL_AGRI_PROD_CODE,
				"code" => $o->prop("prod_code")
			));
			$this->prod_code = $ol->begin();
			$this->inited = 1;
		}
	}
	
	function get_short_data_array($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM agri_prod_data_row_sums WHERE obj_id = '$id'");
		while ($row = $this->db_next())
		{
			$ret[$row["unit"]][$row["year"]][$row["nr"]] = $row["value"];
		}

		return $ret;
	}

	function save_short_data_array($id, $dat)
	{
		$this->db_query("DELETE FROM agri_prod_data_row_sums WHERE obj_id = '$id'");
		foreach($dat as $unit => $udata)
		{
			foreach($udata as $year => $ydata)
			{
				foreach($ydata as $nr => $vl)
				{
					$this->db_query("INSERT INTO agri_prod_data_row_sums(unit,year,nr,value,obj_id)
						VALUES('$unit','$year','$nr','$vl','$id')");
				}
			}
		}
	}

	function init_prods_locs_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "addr",
			"caption" => "Aadress*",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "amt",
			"caption" => "Toote kogus (kilogramm,liiter)",
			"align" => "center"
		));
	}

	function do_prods_locs_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_prods_locs_tbl($t);

		$this->db_query("SELECT * FROM agri_prods_locs WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		while ($row = $this->db_next())
		{
			$t->define_data(array(
				"addr" => html::textarea(array(
					"name" => "data[".$row["id"]."][addr]",
					"value" => $row["addr"],
					"rows" => 5,
					"cols" => 30
				)),
				"amt" => html::textbox(array(
					"name" => "data[".$row["id"]."][amt]",
					"value" => $row["amt"],
					"size" => 10
				)),
			));
		}

		for ($i = 0; $i < 2; $i++)
		{
			$t->define_data(array(
				"addr" => html::textarea(array(
					"name" => "new[$i][addr]",
					"value" => "",
					"rows" => 5,
					"cols" => 30
				)),
				"amt" => html::textbox(array(
					"name" => "new[$i][amt]",
					"value" => "",
					"size" => 10
				)),
			));
		}

		$t->set_sortable(false);
	}

	function save_prods_locs_tbl($arr)
	{
		$this->db_query("DELETE FROM agri_prods_locs WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		$awa = new aw_array($arr["request"]["data"]);
		foreach($awa->get() as $dat)
		{
			if ($dat["addr"] != "")
			{
				$this->db_query("INSERT INTO agri_prods_locs (obj_id, addr, amt) 
					VALUES('".$arr["obj_inst"]->id()."','$dat[addr]','$dat[amt]')");
			}
		}

		$awa = new aw_array($arr["request"]["new"]);
		foreach($awa->get() as $dat)
		{
			if ($dat["addr"] != "")
			{
				$this->db_query("INSERT INTO agri_prods_locs (obj_id, addr, amt) 
					VALUES('".$arr["obj_inst"]->id()."','$dat[addr]','$dat[amt]')");
			}
		}

	}

	function init_prods_owners_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "addr",
			"caption" => "Omaniku kontaktandmed*",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "amt",
			"caption" => "Toote kogus (kilogramm,liiter)",
			"align" => "center"
		));
	}

	function do_prods_owners_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_prods_owners_tbl($t);

		$this->db_query("SELECT * FROM agri_prods_owners WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		while ($row = $this->db_next())
		{
			$t->define_data(array(
				"addr" => html::textarea(array(
					"name" => "data[".$row["id"]."][addr]",
					"value" => $row["addr"],
					"rows" => 5,
					"cols" => 30
				)),
				"amt" => html::textbox(array(
					"name" => "data[".$row["id"]."][amt]",
					"value" => $row["amt"],
					"size" => 10
				)),
			));
		}

		for ($i = 0; $i < 2; $i++)
		{
			$t->define_data(array(
				"addr" => html::textarea(array(
					"name" => "new[$i][addr]",
					"value" => "",
					"rows" => 5,
					"cols" => 30
				)),
				"amt" => html::textbox(array(
					"name" => "new[$i][amt]",
					"value" => "",
					"size" => 10
				)),
			));
		}

		$t->set_sortable(false);
	}

	function save_prods_owners_tbl($arr)
	{
		$this->db_query("DELETE FROM agri_prods_owners WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		$awa = new aw_array($arr["request"]["data"]);
		foreach($awa->get() as $dat)
		{
			if ($dat["addr"] != "")
			{
				$this->db_query("INSERT INTO agri_prods_owners (obj_id, addr, amt) 
					VALUES('".$arr["obj_inst"]->id()."','$dat[addr]','$dat[amt]')");
			}
		}

		$awa = new aw_array($arr["request"]["new"]);
		foreach($awa->get() as $dat)
		{
			if ($dat["addr"] != "")
			{
				$this->db_query("INSERT INTO agri_prods_owners (obj_id, addr, amt) 
					VALUES('".$arr["obj_inst"]->id()."','$dat[addr]','$dat[amt]')");
			}
		}

	}

	function init_special_left_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "year",
		));

		$t->define_field(array(
			"name" => "left",
			"caption" => "J&auml;&auml;k perioodi l&otilde;pus (kell 24:00)"
		));

		$t->define_field(array(
			"name" => "left_com",
			"caption" => "Sellest ettev&otilde;tte omanduses olev"
		));
	}

	function save_special_left_tbl($arr)
	{
		$this->db_query("DELETE FROM agri_prod_data_left WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		if (is_array($arr["request"]["data"]))
		{
			foreach($arr["request"]["data"] as $type => $tdata)
			{
				foreach($tdata as $year => $val)
				{
					$this->db_query("INSERT INTO agri_prod_data_left (obj_id, type, year, value) 
						VALUES('".$arr["obj_inst"]->id()."','$type','$year','$val')");
				}
			}
		}
	}

	function do_special_left_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_special_left_tbl($t);

		$data = array();
		$this->db_query("SELECT * FROM agri_prod_data_left WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		while ($row = $this->db_next())
		{
			$data[$row["type"]][$row["year"]] = $row["value"];
		}

		$sum = 0;
		for ($y = 0; $y < 4; $y++)
		{
			$t->define_data(array(
				"year" => "30.04.200".$y,
				"left" => html::textbox(array(
					"name" => "data[left][$y]",
					"value" => $data["left"][$y],
					"size" => 6
				))." kg",
				"left_com" => html::textbox(array(
					"name" => "data[left_com][$y]",
					"value" => $data["left_com"][$y],
					"size" => 6
				))." kg",
			));
			$sum += $data["left_com"][$y];
		}

		$t->define_data(array(
			"period" => "",
			"left" => "Keskmine j&auml;&auml;k:",
			"left_com" => number_format($sum / 4.0, 3)." kg"
		));

		$t->set_sortable(false);
	}

	function init_over_left_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "desc",
		));

		$t->define_field(array(
			"name" => "left",
			"caption" => "&Uuml;leliigsed varud"
		));
	}

	function save_over_left_tbl($arr)
	{
		$this->db_query("DELETE FROM agri_prod_data_over WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		if (is_array($arr["request"]["data"]))
		{
			foreach($arr["request"]["data"] as $type => $tdata)
			{
				foreach($tdata as $nr => $val)
				{
					$this->db_query("INSERT INTO agri_prod_data_over (obj_id, type, nr, value) 
						VALUES('".$arr["obj_inst"]->id()."','$type','$nr','$val')");
				}
			}
		}
	}
	function do_over_left_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_over_left_tbl($t);

		$data = array();
		$this->db_query("SELECT * FROM agri_prod_data_over WHERE obj_id = '".$arr["obj_inst"]->id()."'");
		while ($row = $this->db_next())
		{
			$data[$row["type"]][$row["nr"]] = $row["value"];
		}

		foreach($this->rs_lt as $nr => $desc)
		{
			$t->define_data(array(
				"desc" => $desc,
				"left" => html::textbox(array(
					"name" => "data[over_left][$nr]",
					"value" => $data["over_left"][$nr],
					"size" => 6
				))." kg",
			));
		}

		$t->define_data(array(
			"desc" => html::textbox(array(
				"name" => "data[over_left_desc][7]",
				"value" => $data["over_left_desc"][7],
			)),
			"left" => html::textbox(array(
				"name" => "data[over_left][7]",
				"value" => $data["over_left"][7],
				"size" => 6
			))." kg",
		));

		$t->define_data(array(
			"desc" => html::textbox(array(
				"name" => "data[over_left_desc][8]",
				"value" => $data["over_left_desc"][8],
			)),
			"left" => html::textbox(array(
				"name" => "data[over_left][8]",
				"value" => $data["over_left"][8],
				"size" => 6
			))." kg",
		));

		$t->set_sortable(false);
	}
}
?>
