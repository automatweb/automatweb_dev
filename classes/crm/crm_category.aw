<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_category.aw,v 1.14 2008/12/11 17:06:02 markop Exp $
// crm_category.aw - Kategooria 
/*

@tableinfo aw_account_balances master_index=oid master_table=objects index=aw_oid

@classinfo syslog_type=ST_CRM_CATEGORY relationmgr=yes maintainer=markop

@default table=objects
@default group=general

	@property jrk type=textbox size=5 table=objects field=jrk
	@caption J&auml;rjekord

	@property img_upload type=releditor reltype=RELTYPE_IMAGE props=file,file_show
	@caption Pilt

	@property extern_id type=hidden field=meta method=serialize 

	//@property jrk type=textbox size=4
	//@caption J&auml;rk

@groupinfo list caption="Nimekiri" submit=no
@default group=list

	@property list type=hidden store=no

	@property list_tb type=toolbar no_caption=1 store=no

	@property list_tbl type=table no_caption=1 store=no

@property balance type=hidden table=aw_account_balances field=aw_balance

@groupinfo import caption="Import"
@default group=import

	@property file type=fileupload store=no form=+emb
	@caption Fail

	@property import_tb type=toolbar no_caption=1 store=no
	@property import_tbl type=table no_caption=1 store=no

	@property import_button type=button store=no no_caption=1 parent=import
	@caption Impordi

@reltype IMAGE value=1 clid=CL_IMAGE
@caption Pilt

@reltype CATEGORY value=2 clid=CL_CRM_CATEGORY
@caption Alam kategooria

@reltype CUSTOMER value=3 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

*/

class crm_category extends class_base
{
	function crm_category()
	{
		$this->init(array(
			"tpldir" => "crm/crm_category",
			"clid" => CL_CRM_CATEGORY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "file":
				if(isset($_SESSION["cust_import"]["file"]))
				return PROP_IGNORE;
				break;
			case "list_tb":
				$t = &$prop["vcl_inst"];
				$t->add_search_button(array(
					"pn" => "list",
					"clid" => CL_CRM_PERSON,
					"multiple" => 1,
				));
				$t->add_button(array(
					"name" => "delete_rels_to",
					"tooltip" => t("Kustuta seosed"),
					"img" => "delete.gif",
					"action" => "delete_rels_to",
					"confirm" => t("Oled kindel, et soovid valitud seosed kustutada?"),
				));
				break;
			case "list_tbl":
				$this->_get_list_tbl($arr);
				break;
			case "import_tbl":
				if(!isset($_SESSION["cust_import"]["file"]))
				{
					return PROP_IGNORE;
				}
				$this->_get_import_tbl($arr);
				break;
			case "import_button":
				if(!$this->change_data_set())
				return PROP_IGNORE;
				$prop["value"] = t("Impordi kliendid!");
				$prop["onclick"] = "javascript:submit_changeform('do_import');";
				break;
		};
		return $retval;
	}

	function dbg($text)
	{
		print $text."<br>";
		flush();
	}

	var $separator = "\";\"";


	/** Customer import
		@attrib name=do_import all_args=1
	**/
	function do_import($arr)
	{
		aw_set_exec_time(AW_LONG_PROCESS);
		ini_set("memory_limit", "800M");
		$cd = $arr["set_row_data_prop"];//data to be changed
		$data = $this->get_import_data();
 		$data_array = explode("\n" , $data);
		$u = get_instance(CL_USER);
		$parent = $u->get_current_company();

		$row = 1;

		$name_field = $this->get_field_by_name("name");arr($name_field);
		$reg_field = $this->get_field_by_name("reg_nr");
		arr("kokku: ".sizeof($data_array));
		$shorts = $this->get_corform_shorts();
		foreach($data_array as $row => $d)
		{
			if(!$_SESSION["cust_import"]["set_rows"][$row])//pole valitud muutmiseks
			{
				continue;
			}
			$prop_list = explode($this->separator , $d);

			$customer = "";

			if($prop_list[$reg_field] || $prop_list[$name_field])
			{
				$cust_id = $this->seach_cust_by_reg_code($prop_list[$reg_field] , $prop_list[$name_field]);
				if($this->can("view" , $cust_id))
				{
					$customer = obj($cust_id);
				}
			}
			if(!is_object($customer))
			{
				$customer = new object();
				$customer->set_class_id(CL_CRM_COMPANY);
				$customer->set_parent($parent);
				$customer->save();
			}
			arr("Klient " . $customer->id());
			$rel = $customer->get_customer_relation(null, true);
			$sec_name = $sec_code = $county = $city = $address = $contact_name = $contact_phone = $contact_mail = $contact_section = $contact_rank = "";

			foreach($prop_list as $prop_id => $val)
			{
				if($cd[$row][$prop_id])
				{
					$val = trim($val, "\"");
					$pn = $_SESSION["cust_import"]["field_props"][$prop_id];
					$this->dbg("salvestab ".$val. " propiks ".$pn);
					switch($pn)
					{
						case "name":
//							$tail = substr($val, $last_ent);
//							if(in_array(trim($tail) , $shorts))
//							{
//								
//							}
							$customer->set_name($val);
							break;
						case "sector.code":
							$sec_code = $val;
							break;
						case "sector.name":
							$sec_name = $val;
							break;
						case "reg_nr":
							$customer->set_prop("reg_nr" , $val);
							break;
						case "county":
							$county = $val;
							break;
						case "city":
							$city = $val;
							break;
						case "address":
							$address = $val;
							break;
						case "mail":
							if($cd[$row][$prop_id] == "2")
							{
								$customer->add_mail($val);
							}
							else
							{
								$customer->change_mail($val);
							}
							break;
						case "url":
							if($cd[$row][$prop_id] == "2")
							{
								$customer->add_url($val);
							}
							else
							{
								$customer->change_url($val);
							}
							break;
						case "contact.name":
							$contact_name = $val;
							break;
						case "contact.rank":
							$contact_rank = $val;
							break;
						case "contact.section":
							$contact_section = $val;
							break;
						case "contact.mail":
							$contact_mail = $val;
							break;
						case "contact.phone":
							$contact_phone = $val;
							break;
						case "phone":
							if($cd[$row][$prop_id] == "2")
							{
								$customer->add_phone($val);
							}
							else
							{
								$customer->change_phone($val);
							}
							break;
					}
				}
			}

			if($sec_name || $sec_code)
			{
				$customer->add_sector(array(
					"name" => $sec_name,
					"sec_code" => $sec_code,
					"parent" => $parent,
					));
					}
			if($county || $city || $address)
			{//oleks vaja kuidagi m22rata aadressi elementidele kataloogi kus neid ei kustutata 2ra lambist
				$customer->add_address(array(
					"county" => $county,
					"city" => $city,
					"address" => $address,
				));
			}
			if($contact_name || $contact_phone || $contact_mail || $contact_section || $contact_rank)
			{
				$cp = $customer->add_worker_data(array(
					"worker" =>  $contact_name,
					"profession" =>  $contact_rank,
					"section" =>  $contact_section,
					"mail" => $contact_mail,
					"phone" => $contact_phone,
					"parent" => $parent,
				));
				$rel->set_prop("buyer_contact_person" , $cp);
				$rel->save();
			}
			$customer->save();arr($customer); arr($customer->prop("reg_nr"));
			$customer->add_category($arr["id"]);
			$row++;

//			if($row>100)
//			{
//				break;
//			}
		}

		echo "<!--\n";
		print get_time_stats();
		echo "-->\n";
		$this->reset_import_data();

		

		print html::href(array("url" => $arr["post_ru"] , "caption" => t("Tagasi")));
		die();
	}

	function import_prop_caption($x)
	{
		$options =array(
			"" => t("-- Vali omadus --"),
			"name" => t("Kliendi Nimi"),
			"sector.code" => t("Tegevusala kood"),
			"reg_nr" => t("Registri_kood"),
			"sector.name" => t("Tegevusala nimi"),
			"county" => t("Maakond"),
			"city" => t("Linn"),
			"address" => t("Aadress"),
			"mail" => t("E-mail"),
			"url" => t("Veebiaadress"),
			"contact.name" => t("Kontaktisiku nimi"),
			"contact.rank" => t("Kontaktisiku amet"),
			"contact.section" => t("Kontaktisiku osakond"),
			"contact.mail" => t("Kontaktisiku E-post"),
			"contact.phone" => t("Kontaktisiku telefon"),
			"phone" => t("Telefon"),
		);
		if($this->prop_names_set())
		{
			return $options[$_SESSION["cust_import"]["field_props"][$x]]."<br><br>".
				(!$this->change_data_set() ? 
					t("Vaikimisi:")."<br>".html::select(array(
						"name" => "set_data_prop[".$x."]",
						"options" => $this->get_row_prop_chooser_options($x),
					))
				 : "");
		}
		return html::select(array(
			"options" => $options,
			"name" => "data_prop_name[".$x."]",
		));
	}

	function get_row_prop_chooser_options($prop_id)
	{
		$addable_props = array("mail" ,"url", "phone");
		if(in_array($_SESSION["cust_import"]["field_props"][$prop_id] , $addable_props))
		{
			return array(
				t("Ignoreeri"), t("Muuda"), t("Lisa")
			);
		}
		else return array(
				t("Ignoreeri"), t("Muuda")
		);
		
	}

	function get_row_prop_chooser($x , $y , $data , $ex = null)
	{
		if(!$this->change_data_set() || !strlen($data)) return "";
		if(!isset($_SESSION["cust_import"]["set_rows"][$x])|| !isset($_SESSION["cust_import"]["set_props"][$y]))
		{
			return "";
		}
		$options = $this->get_row_prop_chooser_options($y);
		return "<br>".html::select(array(
			"name" => "set_row_data_prop[".$x."][".$y."]",
			"options" => $options,
			"value" => $_SESSION["cust_import"]["set_props"][$y],
		));
	}

	function prop_names_set()
	{
		if(isset($_SESSION["cust_import"]["field_props"]))
		{
			return 1;
		}
		return 0;
	}

	function get_field_by_name($fn)
	{
		if(isset($_SESSION["cust_import"]["field_props"]))
		{
			foreach($_SESSION["cust_import"]["field_props"] as $key => $name)
			{
				if($name == $fn)
				{
					return $key;
				}
			}
			
		}
		return null;
	}

	function change_data_set()
	{
		if(isset($_SESSION["cust_import"]["set_props"]) && isset($_SESSION["cust_import"]["set_rows"]))
		{
			return 1;
		}
		return 0;
	}

	function get_row_color($x,$exists)
	{
		if(isset($_SESSION["cust_import"]["set_rows"]))
		{
			if(isset($_SESSION["cust_import"]["set_rows"][$x]))
			{
				return "#CCFF66";
			}
			return "#BBBBBB";
		}
		elseif($exists)
		{
			return "#BBBBBB";
		}

		return "";
	}

	function _get_import_tbl($arr)
	{
		$data = $this->get_import_data();
		$data_array = explode("\n" , $data);
		$prop_list = explode($this->separator , $data_array[0]);
		$x = 0;
		$t = &$arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "jrk",
			"caption" => t("Jrk"),
			"chgbgcolor" => "color",
		));

		if($this->prop_names_set() && !$this->change_data_set())
		{
			$t->define_chooser(array(
				"name" => "set_row",
				"caption" => t("Lisa"),
				"field" => "row_id",
				"chgbgcolor" => "color",
			));
		}
		if($this->prop_names_set())
		{
			$t->define_field(array(
				"name" => "oid",
				"caption" => t("ID"),
				"chgbgcolor" => "color",
			));
		}
		while ($x < sizeof($prop_list))
		{
			if(!$this->prop_names_set() || $_SESSION["cust_import"]["field_props"][$x])
			{
				$t->define_field(array(
					"name" => "prop_".$x,
					"caption" => $this->import_prop_caption($x),
					"chgbgcolor" => "color",
				));
			}
			$x++;
		}
		$reg_field = $this->get_field_by_name("reg_nr");
		$name_field = $this->get_field_by_name("name");

		$x = 1;
		$to = 10;
		if($this->prop_names_set())
		{
		//	$to = 100;
			$to = sizeof($data_array) - 1;
		}

		while ($x < $to)
		{
			$data = array();
			$y = 0;
			$prop_list = explode($this->separator , $data_array[$x]);
			$customer_exists = $this->seach_cust_by_reg_code(trim($prop_list[$reg_field] , "\""), trim($prop_list[$name_field] , "\""));
			while ($y < sizeof($prop_list))
			{
				$row_data["prop_".$y] = trim($prop_list[$y] , "\"").$this->get_row_prop_chooser($x , $y , trim($prop_list[$y] , "\""), $customer_exists);
				$y++;
			}
			$row_data["color"] = $this->get_row_color($x,$customer_exists);
			$row_data["row_id"] = $x;
			$row_data["jrk"] = $x;
			$row_data["oid"] = $customer_exists;
			$t->define_data($row_data);
			$x++;
		}
		$t->set_sortable(false);
	}

	private function get_corform_shorts()
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPFORM,
			"site_id" => array(),
			"lang_id" => array(),
		));

		$ret = array();
		foreach($ol->arr() as $o)
		{
			$ret[$o->id()] = $o->prop("shortname");
		}
		return $ret;
	}

	private function seach_cust_by_reg_code($reg , $name)
	{
		if(!($reg || $name))
		{
			return null;
		}
		if($reg)
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY,
				"site_id" => array(),
				"lang_id" => array(),
				"reg_nr" => $reg,
			));
		}
		else
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY,
				"site_id" => array(),
				"lang_id" => array(),
				"name" => $name,
			));
		}
		return reset($ol->ids());
	}

	function _get_list_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		$co_list = $arr["obj_inst"]->get_category_orgs();

		$conns = $arr["obj_inst"]->connections_to(array(
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_CATEGORY",		// RELTYPE_CATEGORY
		));
		foreach($conns as $conn)
		{
			$from = $conn->from();
			$t->define_data(array(
				"oid" => $from->id(),
				"name" => html::href(array(
					"caption" => $from->name(),
					"url" => $this->mk_my_orb("change", array("id" => $from->id(), "return_url" => get_ru()), CL_CRM_PERSON),
				)),
			));
		}

		foreach($co_list->arr() as $co)
		{
			$t->define_data(array(
				"oid" => $co->id(),
				"name" => html::href(array(
					"caption" => $co->name(),
					"url" => $this->mk_my_orb("change", array("id" => $co->id(), "return_url" => get_ru()), CL_CRM_COMPANY),
				)),
			));
		}
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "list":
				$ps = explode(",", $prop["value"]);
				foreach($ps as $p)
				{
					$c = new connection(array(
						"to" => $arr["obj_inst"]->id(),
						"from" => $p,
						"reltype" => 80,		// RELTYPE_CATEGORY
					));
					$c->save();
				}
				break;
			case "file":
				if(isset($_FILES["file"]["tmp_name"]) && file_exists($_FILES["file"]["tmp_name"]))
				{
					$_SESSION["cust_import"]["file"] = file_get_contents($_FILES["file"]["tmp_name"]);
				}
				break;
			case "import_tbl":
				if(isset($arr["request"]["data_prop_name"]))
				{
					$_SESSION["cust_import"]["field_props"] = $arr["request"]["data_prop_name"];
				}
				if(isset($arr["request"]["set_data_prop"]))
				{
					$_SESSION["cust_import"]["set_props"] = $arr["request"]["set_data_prop"];
				}
				if(isset($arr["request"]["set_row"]))
				{
					$_SESSION["cust_import"]["set_rows"] = $arr["request"]["set_row"];
				}
				break;
		}
		return $retval;
	}

	private function get_import_data()
	{
		if(isset($_SESSION["cust_import"]["file"]))
		{
			return $_SESSION["cust_import"]["file"];
		}
		else return "";
	}

	private function reset_import_data()
	{
		unset($_SESSION["cust_import"]["field_props"]);
		unset($_SESSION["cust_import"]["set_rows"]);
		unset($_SESSION["cust_import"]["file"]);
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
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

	function do_db_upgrade($t, $f)
	{
		if ($t == "aw_account_balances" && $f == "")
		{
			$this->db_query("CREATE TABLE $t (aw_oid int primary key, aw_balance double)");
			// also, create entries in the table for each existing object
			$this->db_query("SELECT oid FROM objects WHERE class_id IN (".CL_CRM_CATEGORY.",".CL_CRM_COMPANY.",".CL_PROJECT.",".CL_TASK.",".CL_CRM_PERSON.",".CL_BUDGETING_FUND.",".CL_SHOP_PRODUCT.",".CL_BUDGETING_ACCOUNT.")");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				$this->db_query("INSERT INTO $t(aw_oid, aw_balance) values($row[oid], 0)");
				$this->restore_handle();
			}
			return true;
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	/**
		@attrib name=delete_rels_to
	**/
	function delete_rels_to($arr)
	{
		foreach($arr["sel"] as $id)
		{
			$cs = connection::find(array(
				"to" => $arr["id"],
				"from" => $id,
				"reltype" => "RELTYPE_CATEGORY",
			));
			foreach($cs as $c_id)
			{
				$c = new connection($c_id);
				$c->delete();
			}
		}
		return $arr["post_ru"];
	}
}
?>
