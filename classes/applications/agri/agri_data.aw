<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/agri/Attic/agri_data.aw,v 1.1 2004/03/28 21:45:14 kristo Exp $
// agri_data.aw - Agri andmed 
/*

@tableinfo agri_data index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_AGRI_DATA no_comment=1 no_status=1 no_name=1

@default table=objects
@default group=general
@default table=agri_data

@property regcode type=textbox 
@caption &Auml;riregistrikood

@property utype type=hidden 
@caption Kasutaja t&uuml;&uuml;p

///// fie data first tab

@property first_name type=textbox
@caption Eesnimi

@property last_name type=textbox 
@caption Perekonnanimi

@property isikukood type=textbox 
@caption Isikukood

@property business_name type=textbox
@caption &Auml;rinimi


///// company data first tab

@property company_name type=textbox 
@caption Nimi

@property rep_first_name type=textbox 
@caption Esindaja eesnimi

@property rep_last_name type=textbox 
@caption Esindaja perekonnanimi


////// ARUANDVA ISIKU AADRESS tab
@groupinfo rep_addr caption="AADRESS"

@groupinfo rep_addr_pers parent=rep_addr caption="Elukoht / Asukoht"
@default group=rep_addr_pers

@property rep_addr_pers_state type=select 
@caption Maakond

@property rep_addr_pers_city type=select 
@caption Vald/linn

@property rep_addr_pers_village type=textbox 
@caption K&uuml;la/alev/alevik

@property rep_addr_pers_street type=textbox
@caption T&auml;nav/maja

@property rep_addr_pers_idx type=textbox
@caption Postiindeks

@property rep_addr_pers_phone type=textbox
@caption Telefon, Faks

@property rep_addr_pers_email type=textbox
@caption E-post


@groupinfo rep_addr_com parent=rep_addr caption="Ettev&otilde;tte asukoht"
@default group=rep_addr_com

@property rep_addr_com_state type=select 
@caption Maakond

@property rep_addr_com_city type=select 
@caption Vald/linn

@property rep_addr_com_village type=textbox 
@caption K&uuml;la/alev/alevik

@property rep_addr_com_street type=textbox
@caption T&auml;nav/maja

@property rep_addr_com_idx type=textbox
@caption Postiindeks

@property rep_addr_com_phone type=textbox
@caption Telefon, Faks

@property rep_addr_com_email type=textbox
@caption E-post

@groupinfo act_dat caption="ANDMED MAJANDUSTEGEVUSE KOHTA"
@default group=act_dat

@property main_act type=textbox
@caption P&otilde;hitegevusala

@property other_act type=textarea rows=5 cols=30
@caption Muu majandustegevus

@groupinfo adds caption="TOOTED"

@default group=adds

@property prods type=table
@caption Sisestatud tooted

@property add_prod type=textbox store=no
@caption Uue toote sisestamiseks kirjuta tootekood

@property add_prod_inf type=text store=no
@caption Spikker

@groupinfo pdf caption="PDF" submit=no
@default group=pdf

@property pdf type=text no_caption=1 store=no


@reltype PROD_DATA value=1 clid=CL_AGRI_PROD_DATA
@caption toote info

*/

class agri_data extends class_base
{
	var $states = array(
		"Harjumaa",
		"Hiiumaa",
		"Ida-Virumaa",
		"Jõgevamaa",
		"Järvamaa",
		"Läänemaa",
		"Lääne-Virumaa",
		"Põlvamaa",
		"Pärnumaa",
		"Raplamaa",
		"Saaremaa",
		"Tartumaa",
		"Valgamaa",
		"Viljandimaa",
		"Võrumaa",
	);

	var $cities = array(
		"Elva",
		"Haapsalu",
		"Jõgeva",
		"Jõhvi",
		"Kallaste",
		"Keila",
		"Kilingi-Nõmme",
		"Kiviõli",
		"Kohtla-Järve",
		"Kunda",
		"Kuressaare",
		"Kärdla",
		"Lihula",
		"Loksa",
		"Maardu",
		"Mustvee",
		"Mõisaküla",
		"Narva",
		"Narva-Jõesuu",
		"Paide",
		"Paldiski",
		"Põltsamaa",
		"Põlva",
		"Pärnu",
		"Püssi",
		"Rakvere",
		"Saue",
		"Sillamäe",
		"Sindi",
		"Suure-Jaani",
		"Tallinn",
		"Tamsalu",
		"Tapa",
		"Tartu",
		"Tõrva",
		"Türi",
		"Valga",
		"Viljandi",
		"Võhma",
		"Võru",
		);

	function agri_data()
	{
		$this->init(array(
			"tpldir" => "applications/agri/agri_data",
			"clid" => CL_AGRI_DATA
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "name":
				return PROP_IGNORE;

			case "first_name":
			case "last_name":
			case "isikukood":
			case "business_name":
				if ($arr["obj_inst"]->prop("utype") == "com")
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "rep_first_name":
			case "rep_last_name":
			case "company_name":
				if ($arr["obj_inst"]->prop("utype") == "pri")
				{
					$retval = PROP_IGNORE;
				}
				break;

			case "rep_addr_pers_state":
			case "rep_addr_com_state":
				$prop["options"] = $this->states;
				break;

			case "rep_addr_pers_city":
			case "rep_addr_com_city":
				$prop["options"] = $this->cities;
				break;

			case "prods":
				$this->do_prods_tbl($arr);
				break;

			case "pdf":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("make_pdf", array("id" => $arr["obj_inst"]->id())),
					"caption" => "Prindi pdf fail"
				));
				break;
		
			case "add_prod_inf":
				$prop["value"] = html::href(array(
					"url" => "javascript:void(0)",
					"onClick" => "aw_popup_scroll(\"".$this->mk_my_orb("spikker", array("id" => $arr["obj_inst"]->id()), "", false, true)."\", 300, 500)",
					"caption" => "Tootekoodide nimekiri"
				));
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "add_prod":
				if ($prop["value"] != "")
				{
					// check if product exists
					$ol = new object_list(array(
						"class_id" => CL_AGRI_PROD_CODE,
						"code" => $prop["value"]
					));
					if ($ol->count() < 1)
					{
						$prop["error"] = "Sellist tootekoodi pole olemas!";
						return PROP_FATAL_ERROR;
					}
					$this->do_add_prod_to_entry($arr);
				}
				break;

			case "regcode":
				$arr["obj_inst"]->set_name($prop["value"]);
				break;
		}
		return $retval;
	}	

	function init_prods_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "code",
			"caption" => "Tootekood",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "edit",
			"caption" => "Muuda andmeid",
			"align" => "center"
		));
	}

	function do_prods_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_prods_tbl($t);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_PROD_DATA)) as $c)
		{
			$to = $c->to();
			$t->define_data(array(
				"name" => $to->name(),
				"code" => $to->prop("prod_code"),
				"edit" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $to->id()), $to->class_id()),
					"caption" => "Muuda"
				))
			));
		}
	}

	function do_add_prod_to_entry(&$arr)
	{
		$o = obj();
		$o->set_class_id(CL_AGRI_PROD_DATA);
		$o->set_parent(aw_ini_get("agri.prod_data_parent"));
		$o->set_prop("prod_code", $arr["prop"]["value"]);
		$o->save();

		// connect
		$arr["obj_inst"]->connect(array(
			"to" => $o->id(),
			"reltype" => RELTYPE_PROD_DATA
		));

		// and now, redirect to editing prod data
		header("Location: ".$this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()));
		die();
	}

	/** creates a pdf from the entered data

		@attrib name=make_pdf

		@param id required type int acl=view
	**/
	function make_pdf($arr)
	{
		// ok, so let's make a pdf from this crap.
		$this->read_template("pdf.tpl");

		$o = obj($arr["id"]);
		foreach($o->properties() as $p => $pd)
		{
			$this->vars(array(
				$p => $o->prop($p)
			));
		}
		$pages = array();

		$str = $this->parse();
		$conv = get_instance("core/converters/html2pdf");
		$pages[] = $conv->convert(array("source" => $str));
		$tmp = $this->make_pdf_add_b($arr);
		foreach($tmp as $page)
		{
			$pages[] = $page;
		}

		$tmp = $this->make_pdf_add_e($arr);
		foreach($tmp as $page)
		{
			$pages[] = $page;
		}

		$tmp = $this->make_pdf_add_f($arr);
		foreach($tmp as $page)
		{
			$pages[] = $page;
		}

		$tmp = $this->make_pdf_add_g($arr);
		foreach($tmp as $page)
		{
			$pages[] = $page;
		}

		// no, wcat thepdfs together, using aip's pdf catter :)
		$fns = array();
		foreach($pages as $page)
		{
			$tn = tempnam(aw_ini_get("server.tmpdir"), "agri");
			$fp = fopen($tn, "w");
			fwrite($fp, $page);
			fclose($fp);

			$fns[] = $tn;
		}

		$res = tempnam(aw_ini_get("server.tmpdir"), "agri");
		$namelist = join(" ", $fns);
		$cmdline = "/www/bin/pdcat -r -b +I -oP $namelist $res";
		exec($cmdline);

		foreach($fns as $fn)
		{
			unlink($fn);
		}
		$final = $this->get_file(array("file" => $res));
		unlink($res);


		header("Content-type: application/pdf");
		die($final);
	}

	function make_pdf_add_b($arr)
	{
		$o = obj($arr["id"]);

		$ret = array();
		foreach($o->connections_from(array("type" => 1 /* PROD_DATA */)) as $c)
		{
			$this->read_template("pdf_add_b.tpl");
			$to = $c->to();
			$apd = get_instance("applications/agri/agri_prod_data");
			$apd->_init($to);
			
			if ($apd->prod_code->prop("period") == 1)
			{
				// long
				$data = $apd->get_long_data_array($to->id());

				$year = "";
				for($y = 0; $y < 5; $y++)
				{
					$td = "";
					if ($y == 2)
					{
						$year .= "<br><br><br><br>";
					}
					if ($y == 4)
					{
						$year .= "<br><br><br><br><Br>";
					}

					if ($y == 4)
					{
						$this->vars(array(
							"text" => "M&otilde;&otilde;t&uuml;hik: <b>krooni</b>"
						));
					}
					else
					{
						$this->vars(array(
							"text" => "M&otilde;&otilde;t&uuml;hik: <b>kilogramm,liiter</b>"
						));
					}
					$td .= $this->parse("TDL");

					// add title
					for ($mon = 0; $mon < 12; $mon++)
					{
						$ry = ($y+$apd->mon_lut[$mon]["y"]);
						if ($y == 4)
						{
							$ry--;
						}
						$this->vars(array(
							"text" => $apd->mon_lut[$mon]["n"].".0".$ry
						));
						$td .= $this->parse("TDL");
					}

					$this->vars(array(
						"text" => "KOKKU"
					));
					$td .= $this->parse("TD");

					$this->vars(array(
						"TD" => $td
					));
					$tr .= $this->parse("TR");
					$td = "";

					// now entry rows
					foreach($apd->entry_lut as $nr => $txt)
					{
						$td = "";
						$this->vars(array(
							"text" => $txt
						));
						$td .= $this->parse("TDL");

						$sum = 0;
						for ($mon = 0; $mon < 12; $mon++)
						{
							$rm = $apd->mon_lut[$mon]["m"];
							$ry = $y + $apd->mon_lut[$mon]["y"];
							$vl = $data["kg"][$ry][$rm][$nr];
							if ($y == 4)
							{
								$ry--;
								$vl = $data["eek"][$ry][$rm][$nr];
							}

							$this->vars(array(
								"text" => (double)$vl
							));
							$td .= $this->parse("TD");
							$sum += $vl;
						}
						$this->vars(array(
							"text" => $sum
						));
						$td .= $this->parse("TD");

						$this->vars(array(
							"TD" => $td
						));
						$tr .= $this->parse("TR");
					}

					$this->vars(array(
						"TR" => $tr,
						"prod_name" => $apd->prod_code->prop("name"),
						"prod_code" => $apd->prod_code->prop("code"),
						"date" => date("d.m.Y")
					));
					$tr = "";
					$year .= $this->parse("LONG_YEAR");
				}

				$this->vars(array(
					"LONG_YEAR" => $year,
					"SHORT_YEAR" => ""
				));
				$conv = get_instance("core/converters/html2pdf");
				$tmp = $conv->convert(array(
					"source" => $this->parse(),
					"landscape" => 1,
					"no_numbers" => 1
				));			
				$ret[] = $tmp;
			}
			else
			{
				// short
				$data = $apd->get_short_data_array($to->id());

				$year = "";
				for($y = 0; $y < 4; $y++)
				{
					if ($y == 3)
					{
						$year .= "<br><br><br><br>";
					}
					$year .= "<b>Vaadeldav periood: 01.05.200".$y." - 30.04.200".($y+1)."<Br>";

					$td = "";
					$this->vars(array(
						"text" => "&nbsp;"
					));
					$td .= $this->parse("STDL");

					$this->vars(array(
						"text" => "<b>Kogus, kg</b>"
					));
					$td .= $this->parse("STDL");

					if ($y == 3)
					{
						$this->vars(array(
							"text" => "<b>V&auml;&auml;rtus, EEK</b>"
						));
						$td .= $this->parse("STDL");
					}

					$this->vars(array(
						"STD" => $td
					));
					$tr .= $this->parse("STR");
					$td = "";

					// now entry rows
					foreach($apd->entry_lut as $nr => $txt)
					{
						$td = "";
						$this->vars(array(
							"text" => $txt
						));
						$td .= $this->parse("STDL");

						$vl = $data["kg"][$y][$nr];

						$this->vars(array(
							"text" => (double)$vl
						));
						$td .= $this->parse("STD");

						if ($y == 3)
						{
							// also eek for last year
							$vl = $data["eek"][$y][$nr];

							$this->vars(array(
								"text" => (double)$vl
							));
							$td .= $this->parse("STD");
						}

						$this->vars(array(
							"STD" => $td
						));
						$tr .= $this->parse("STR");
					}

					$this->vars(array(
						"STR" => $tr,
						"prod_name" => $apd->prod_code->prop("name"),
						"prod_code" => $apd->prod_code->prop("code"),
						"date" => date("d.m.Y")
					));
					$tr = "";
					$year .= $this->parse("SHORT_YEAR");
				}

				$this->vars(array(
					"LONG_YEAR" => "",
					"SHORT_YEAR" => $year
				));
				$conv = get_instance("core/converters/html2pdf");
				$tmp = $conv->convert(array(
					"source" => $this->parse(),
					"no_numbers" => 1
				));			
				$ret[] = $tmp;
			}
		}

		return $ret;
	}

	function make_pdf_add_e($arr)
	{
		$o = obj($arr["id"]);

		$ret = array();
		foreach($o->connections_from(array("type" => 1 /* PROD_DATA */)) as $c)
		{
			$this->read_template("pdf_add_e.tpl");

			$to = $c->to();
			$apd = get_instance("applications/agri/agri_prod_data");
			$apd->_init($to);
			$this->vars(array(
				"prod_name" => $apd->prod_code->prop("name"),
				"prod_code" => $apd->prod_code->prop("code"),
				"date" => date("d.m.Y")
			));

			$l = "";
			$this->db_query("SELECT * FROM agri_prods_locs WHERE obj_id = '".$to->id()."'");
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"addr" => nl2br($row["addr"]),
					"amt" => $row["amt"]
				));
				$l .= $this->parse("LINE");
			}

			$this->vars(array(
				"LINE" => $l
			));

			$conv = get_instance("core/converters/html2pdf");
			$tmp = $conv->convert(array(
				"source" => $this->parse(),
				"no_numbers" => 1
			));			
			$ret[] = $tmp;
		}

		return $ret;
	}

	function make_pdf_add_g($arr)
	{
		$o = obj($arr["id"]);

		$ret = array();
		foreach($o->connections_from(array("type" => 1 /* PROD_DATA */)) as $c)
		{
			$this->read_template("pdf_add_g.tpl");

			$to = $c->to();
			$apd = get_instance("applications/agri/agri_prod_data");
			$apd->_init($to);
			$this->vars(array(
				"prod_name" => $apd->prod_code->prop("name"),
				"prod_code" => $apd->prod_code->prop("code"),
				"date" => date("d.m.Y")
			));

			$l = "";
			$this->db_query("SELECT * FROM agri_prods_owners WHERE obj_id = '".$to->id()."'");
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"addr" => nl2br($row["addr"]),
					"amt" => $row["amt"]
				));
				$l .= $this->parse("LINE");
			}

			$this->vars(array(
				"LINE" => $l
			));

			$conv = get_instance("core/converters/html2pdf");
			$tmp = $conv->convert(array(
				"source" => $this->parse(),
				"no_numbers" => 1
			));			
			$ret[] = $tmp;
		}

		return $ret;
	}

	function make_pdf_add_f($arr)
	{
		$o = obj($arr["id"]);

		$ret = array();
		foreach($o->connections_from(array("type" => 1 /* PROD_DATA */)) as $c)
		{
			$this->read_template("pdf_add_f.tpl");

			$to = $c->to();
			$apd = get_instance("applications/agri/agri_prod_data");
			$apd->_init($to);
			$this->vars(array(
				"prod_name" => $apd->prod_code->prop("name"),
				"prod_code" => $apd->prod_code->prop("code"),
				"date" => date("d.m.Y")
			));

			$l = "";
			$data = array();
			$this->db_query("SELECT * FROM agri_prod_data_left WHERE obj_id = '".$to->id()."'");
			while ($row = $this->db_next())
			{
				$data[$row["type"]][$row["year"]] = $row["value"];
			}

			$sum = 0;
			for ($y = 0; $y < 4; $y++)
			{
				$this->vars(array(
					"period" => "30.04.200".$y,
					"j_amt" => $data["left"][$y],
					"o_amt" => $data["left_com"][$y]
				));
				$l .= $this->parse("J_LINE");
				$sum += $data["left_com"][$y];
			}

			$data = array();
			$this->db_query("SELECT * FROM agri_prod_data_over WHERE obj_id = '".$to->id()."'");
			while ($row = $this->db_next())
			{
				$data[$row["type"]][$row["nr"]] = $row["value"];
			}

			$d_l = "";
			foreach($apd->rs_lt as $nr => $desc)
			{
				$this->vars(array(
					"d_text" => $desc,
					"d_amt" => $data["over_left"][$nr]
				));
				$d_l .= $this->parse("D_LINE");
			}

			if (!empty($data["over_left_desc"][7]) || !empty($data["over_left"][7]))
			{
				$this->vars(array(
					"d_text" => $data["over_left_desc"][7],
					"d_amt" => $data["over_left"][7]
				));
				$d_l .= $this->parse("D_LINE");
			}

			if (!empty($data["over_left_desc"][8]) || !empty($data["over_left"][8]))
			{
				$this->vars(array(
					"d_text" => $data["over_left_desc"][8],
					"d_amt" => $data["over_left"][8]
				));
				$d_l .= $this->parse("D_LINE");
			}

			$this->vars(array(
				"avg_left" => number_format($sum/ 4, 2),
				"J_LINE" => $l,
				"D_LINE" => $d_l,
				"sp_avg_left" => $to->prop("sp_avg_left"),
				"sp_avg_com" => $to->prop("sp_avg_com"),
				"desc" => nl2br($to->prop("special_expl"))
			));

			$conv = get_instance("core/converters/html2pdf");
			$tmp = $conv->convert(array(
				"source" => $this->parse(),
				"no_numbers" => 1
			));			
			$ret[] = $tmp;
		}

		return $ret;
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "pdf")
		{
			$arr["link"] = $this->mk_my_orb("make_pdf", array("id" => $arr["obj_inst"]->id()));
		}
		return true;
	}

	/** tootekoodise spikker

		@attrib name=spikker

		@param id required type=int acl=view

	**/
	function spikker($arr)
	{
		$o = obj($arr["id"]);

		classload("vcl/table");
		$t = new aw_table(array("layout" => "generic"));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Toote nimi",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "code",
			"caption" => "Toote kood",
			"sortable" => 1
		));

		$ol = new object_list(array(
			"class_id" => CL_AGRI_PROD_CODE,
		));
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$t->define_data(array(
				"name" => $o->name(),
				"code" => $o->prop("code")
			));
		}

		$t->set_default_sortby("name");
		$t->sort_by();
		return $t->draw();
	}
}
?>
