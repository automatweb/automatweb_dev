<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_prisma_import.aw,v 1.14 2005/03/24 09:55:56 kristo Exp $
// mrp_prisma_import.aw - Prisma import 
/*

@classinfo syslog_type=ST_MRP_PRISMA_IMPORT relationmgr=yes no_status=1

@default table=objects
@default group=general

*/

class mrp_prisma_import extends class_base
{
	function mrp_prisma_import()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_prisma_import",
			"clid" => CL_MRP_PRISMA_IMPORT
		));

		$this->prj_flds = array(
			"comment" => "TööNimetus",
			"starttime" => "TööAlgus",
			"due_date" => "TellimuseTähtaeg",
			"project_priority" => "TellimusePrioriteet",
			"format" => "Formaat",
			"sisu_lk_arv" => "Sisu lk arv",
			"kaane_lk_arv" => "Kaane lk arv",
			"sisu_varvid" => "Sisu värvid",
			"sisu_varvid_notes" => "Sisu värvid Notes",
			"sisu_lakk_muu" => "Sisu lakk/muu",
			"kaane_varvid" => "Kaane värvid",
			"kaane_varvid_notes" => "Kaane värvid Notes",
			"kaane_lakk_muu" => "Kaane lakk/muu",
			"sisu_paber" => "Sisu paber",
			"kaane_paber" => "Kaane paber",
			"trykiarv" => "Trükiarv",
			"trykise_ehitus" => "Trükise ehitus",
			"kromaliin" => "Kromalin",
			"makett" => "Makett",
			"naidis" => "Näidis",
			"plaate" => "Plaate",
			"transport" => "Transport",
			"soodustus" => "Soodustus",
			"markused" => "Märkused",
			"allahindlus" => "Allahindlus",
			"vahendustasu" => "Vahendustasu",
			"myygi_hind" => "Muugi hind",
			"sales_priority" => "prioriteet"
		);

	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
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

	/** sync prisma and cur db

		@attrib name=import

	**/
	function import($arr)
	{
		aw_disable_messages();
		set_time_limit(0);
		
		$db = $this->_get_conn();

		// now. sync from them to us

		// get co		
		$co = $this->_get_co();

		// first, customer categories
		$this->_imp_cust_cat($db, $co);

		// then customers
		$this->_imp_cust($db, $co);

		// finally projects
		$this->_imp_proj($db, $co);

		aw_restore_messages();
		die(t("all done"));
	}

	function _imp_cust_cat($db, $co)
	{
		$cats = array("-1" => "Muud");
		$db->db_query("SELECT * FROM kliendituup");
		while ($row = $db->db_next())
		{
			$cats[$row["KliendiTüüpID"]] = $row["KliendiTüüp"];
		}

		// get existing
		$existing = array();
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$t = $c->to();
			if ($t->prop("extern_id"))
			{
				$existing[$c->prop("to")] = $t;
			}
		}

		// diff
		foreach($existing as $o)
		{
			if (!isset($cats[$o->prop("extern_id")]))
			{
				// removed
				$o->delete();
				echo "category ".$o->name()." (".$o->id().") deleted! <br>\n";
				flush();
			}
			else
			if ($cats[$o->prop("extern_id")] != $o->name())
			{
				// modified
				$o->set_name($cats[$o->prop("extern_id")]);
				$o->save();
				echo "category ".$o->name()." (".$o->id().") modified! <br>\n";
				flush();
			}

			unset($cats[$o->prop("extern_id")]);
		}

		foreach($cats as $id => $nm)
		{
			// added
			$o = obj();	
			$o->set_class_id(CL_CRM_CATEGORY);
			$o->set_parent($co->id());
			$o->set_name($nm);
			$o->set_prop("extern_id", $id);
			$o->save();

			$co->connect(array(
				"to" => $o->id(),
				"reltype" => 30
			));
			echo "category ".$o->name()." (".$o->id().") added! <br>\n";
			flush();
		}
	}

	function _get_co()
	{
		$ws = obj(aw_ini_get("prisma.ws"));
		return $ws->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");
	}

	function _get_conn()
	{
		if (!aw_ini_get("prisma.db_server"))
		{
			return NULL;
		}
		$db = new mysql;
		$db->db_connect(aw_ini_get("prisma.db_server"),aw_ini_get("prisma.db_base"),aw_ini_get("prisma.db_user"),aw_ini_get("prisma.db_pass"));
		return $db;
	}

	function _imp_cust($db, $co)
	{
		// get from db
		$cust = array();
		$db->db_query("SELECT * FROM kliendid");
		while ($row = $db->db_next())
		{
			$cust[$row["KliendiID"]] = $row;
		}

		// get existing
		$existing = array();
		$this->_get_exist_cust($co, $existing);

		// diff
		foreach($existing as $o)
		{
			if (!isset($cust[$o->prop("extern_id")]))
			{
				// removed
				$o->delete();
				echo "customer ".$o->name()." (".$o->id().") deleted! <br>\n";
				flush();
			}
			else
			{
				// modified
				$dat = $cust[$o->prop("extern_id")];
				if ($dat["Timestamp"] != $o->meta("imp_ts"))
				{
					$this->_upd_cust_o($o, $dat);
					$o->set_meta("imp_ts", $dat["Timestamp"]);
					$o->save();
					echo "customer ".$o->name()." (".$o->id().") updated! <br>\n";
					flush();
				}
			}

			unset($cust[$o->prop("extern_id")]);
		}
		foreach($cust as $id => $dat)
		{
			// find category 
			if ($dat["KliendiTüüpID"] != "")
			{
				$ol = new object_list(array(
					"class_id" => CL_CRM_CATEGORY,
					"extern_id" => $dat["KliendiTüüpID"]
				));
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_CRM_CATEGORY,
					"extern_id" => -1
				));
			}

			$t = $ol->begin();
			if (!$t)
			{
				continue;
			}
			// added
			$o = obj();	
			$o->set_class_id(CL_CRM_COMPANY);
			$o->set_parent($co->id());
			$o->set_prop("extern_id", $id);
			$o->save();
			$this->_upd_cust_o($o, $dat);
			$o->save();

			$t = $ol->begin();

			$t->connect(array(
				"to" => $o->id(),
				"reltype" => "RELTYPE_CUSTOMER"
			));
			echo "customer ".$o->name()." (".$o->id().") added! <br>\n";
			flush();
		}
	}

	function _get_exist_cust($co, &$existing)
	{
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$this->_req_cust_cat($c->to(), $existing);
		}
	}

	function _req_cust_cat($cat, &$existing)
	{
		foreach($cat->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$this->_req_cust_cat($c->to(), $existing);
		}

		foreach($cat->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
		{
			$existing[$c->prop("to")] = $c->to();
		}
	}

	function _upd_cust_o($o, $dat)
	{
		$o->set_name($dat["KliendiNimi"]);
		if (trim($dat["Kontaktisik"]) != "")
		{
			if (!is_oid($o->prop("firmajuht")) || !$this->can("view", $o->prop("firmajuht")))
			{
				$c = obj();
				$c->set_class_id(CL_CRM_PERSON);
				$c->set_parent($o->id());
				$c->save();
				$o->set_prop("firmajuht", $c->id());
			}
		
			$p = obj($o->prop("firmajuht"));
			list($fn, $ln) = trim(explode(" ", $dat["Kontaktisik"]));
			$p->set_prop("firstname", $fn);
			$p->set_prop("lastname", $ln);
			$p->set_name($dat["Kontaktisik"]);
			$p->save();
		}

		if (!is_oid($o->prop("contact")) || !$this->can("view", $o->prop("contact")))
		{
			$c = obj();
			$c->set_class_id(CL_CRM_ADDRESS);
			$c->set_parent($o->id());
			$c->save();
			$o->set_prop("contact", $c->id());
		}
			
		$a = obj($o->prop("contact"));
		$a->set_name($dat["Aadress"]);
		$a->save();

		$this->_set_rel_prop($a, "linn", CL_CRM_CITY, $dat["Linn"]);
		$this->_set_rel_prop($a, "riik", CL_CRM_COUNTRY, $dat["Riik"]);
		$this->_set_rel_prop($a, "maakond", CL_CRM_COUNTY, $dat["Maakond"]);

		$this->_set_rel_prop($o, "phone_id", CL_CRM_PHONE, $dat["Tel"]);
		$this->_set_rel_prop($o, "telefax_id", CL_CRM_PHONE, $dat["Fax"]);
		$this->_set_rel_prop($o, "email_id", CL_ML_MEMBER, $dat["e-mail"]);

		$o->set_prop("reg_nr", $dat["Kood"]);
		$o->set_comment($dat["Info"]);

		$o->set_prop("priority", $dat["KliendiPrioriteet"]);
	}

	function _set_rel_prop($o, $prop, $rel_clid, $rel_name)
	{
		if (trim($rel_name) != "")
		{
			if (!is_oid($o->prop($prop)) || !$this->can("view", $o->prop($prop)))
			{
				$c = obj();
				$c->set_class_id($rel_clid);
				$c->set_parent($o->id());
				$c->save();
				$o->set_prop($prop, $c->id());
			}
			$c = obj($o->prop($prop));
			$c->set_name($rel_name);
			$c->save();
		}
		return $c;
	}

	function _imp_proj($db, $co)
	{
		classload("date_calc");
		// get db
		$proj = array();
		$db->db_query("
			SELECT 
				*,
				unix_timestamp(TööAlgus) as TööAlgus,
				unix_timestamp(TellimuseTähtaeg) as TellimuseTähtaeg
			FROM 
				tellimused
		");
		while ($row = $db->db_next())
		{
			if ($row["TööAlgus"] < 100000)
			{
				$row["TööAlgus"] = -1;
			}
			if ($row["TellimuseTähtaeg"] < 100000)
			{
				$row["TellimuseTähtaeg"] = -1;
			}

			// if date is at 00:00 hrs, make it 16:00 hrs
			if ((get_day_start($row["TööAlgus"]) - $row["TööAlgus"]) < 120)
			{
				$row["TööAlgus"] = get_day_start($row["TööAlgus"]) + 16 * 3600;
			}
			if ((get_day_start($row["TellimuseTähtaeg"]) - $row["TellimuseTähtaeg"]) < 120)
			{
				$row["TellimuseTähtaeg"] = get_day_start($row["TellimuseTähtaeg"]) + 16 * 3600;
			}
			$proj[$row["TellimuseNr"]] = $row;
		}

		// get existing
		$ol = new object_list(array(
			"class_id" => CL_MRP_CASE,
			"extern_id" => new obj_predicate_compare(OBJ_COMP_GREATER, 0)
		));
		$existing = $ol->arr();

		$ws = obj(aw_ini_get("prisma.ws"));

		// diff
		foreach($existing as $o)
		{
			if (!isset($proj[$o->prop("extern_id")]))
			{
				if ($o->prop("extern_id"))
				{
					// removed
					$o->delete();
					echo "project ".$o->name()." (".$o->id().") deleted! <br>\n";
					flush();
				}
			}
			else
			{
				// modified
				$dat = $proj[$o->prop("extern_id")];
				if ($dat["TimeStamp"] != $o->meta("imp_ts"))
				{
					$this->_upd_proj_o($o, $dat);
					$o->set_meta("imp_ts", $dat["TimeStamp"]);
					$o->set_parent($ws->prop("projects_folder"));
					$o->save();
					if (!$o->is_connected_to(array("to" => $ws->id())))
					{
						$o->connect(array(
							"to" => $ws->id(),
							"reltype" => "RELTYPE_MRP_OWNER"
						));
					}
					echo "project ".$o->name()." (".$o->id().") updated! <br>\n";
					flush();
				}
			}

			unset($proj[$o->prop("extern_id")]);
		}

		foreach($proj as $id => $dat)
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY,
				"extern_id" => $dat["Tellija"]
			));
			$t = $ol->begin();

			if (!$t)
			{
				continue;
			}
			// added
			$o = obj();	
			$o->set_class_id(CL_MRP_CASE);
			$o->set_parent($ws->prop("projects_folder"));
			$o->set_prop("extern_id", $id);
			$o->set_prop("customer", $t->id());
			$o->save();
			$this->_upd_proj_o($o, $dat);
			$o->save();

			$o->connect(array(
				"to" => $ws->id(),
				"reltype" => "RELTYPE_MRP_OWNER"
			));

			$t->connect(array(
				"to" => $o->id(),
				"reltype" => "RELTYPE_CUSTOMER"
			));
			echo "project ".$o->name()." (".$o->id().") added! <br>\n";
			flush();
		}
	}

	function _upd_proj_o($o, $dat)
	{
		$o->set_name($dat["TellimuseNr"]);
		foreach($this->prj_flds as $p => $f)
		{
//			echo "set prop $p => ".$dat[$f]." <br>";
			if ($p == "comment")
			{
				$o->set_comment($dat[$f]);
			}
			else
			{
				$o->set_prop($p, $dat[$f]);
			}
		}
	}

	function write_proj($id)
	{
		$o = obj($id);
		if (!$o->prop("extern_id"))
		{
			return;
		}
		$sets = array();
		foreach($this->prj_flds as $prop => $fld)
		{
			if ($fld == "prioriteet")
			{
				continue;
			}
			$val = $o->prop($prop);
			$this->quote(&$val);
			if ($fld == "TööAlgus" || $fld == "TellimuseTähtaeg")
			{
				// conv to date
				$val = " FROM_UNIXTIME($val) ";
			}
			else
			{
				$val = "'".$val."'";
			}
			$sets[$fld] = $val;
		}

		$sql = "
			UPDATE
				tellimused
			SET
				".join(",", map2("`%s` = %s", $sets))."
			WHERE
				TellimuseNr = ".$o->prop("extern_id");

		$db = $this->_get_conn();
		if ($db)
		{
			$db->db_query($sql);
		}
	}

	function import_project($id)
	{
		// disable msg
		aw_disable_messages();

		// get from db
		$db = $this->_get_conn();
		$co = $this->_get_co();

		$dat = $db->db_fetch_row("
			SELECT 
				*,
				unix_timestamp(TööAlgus) as TööAlgus,
                                unix_timestamp(TellimuseTähtaeg) as TellimuseTähtaeg
			FROM 
				tellimused 
			WHERE 
				TellimuseNr = '$id'
		");
		if ($dat["TööAlgus"] < 100000)
		{
			$dat["TööAlgus"] = -1;
		}
		if ($dat["TellimuseTähtaeg"] < 100000)
		{
			$dat["TellimuseTähtaeg"] = -1;
		}

		classload("date_calc");
		// if date is at 00:00 hrs, make it 16:00 hrs
		if ((get_day_start($dat["TööAlgus"]) - $dat["TööAlgus"]) < 120)
		{
			$dat["TööAlgus"] = get_day_start($dat["TööAlgus"]) + 16 * 3600;
		}
		if ((get_day_start($dat["TellimuseTähtaeg"]) - $dat["TellimuseTähtaeg"]) < 120)
		{
			$dat["TellimuseTähtaeg"] = get_day_start($dat["TellimuseTähtaeg"]) + 16 * 3600;
		}

		// check if we got it
		$ol = new object_list(array(
			"class_id" => CL_MRP_CASE,
			"extern_id" => $id
		));

		$c_ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"extern_id" => $dat["Tellija"]
		));
		if (!$c_ol->count())
		{
			// import new customer
			$t = $this->_imp_new_cust($co,$dat["Tellija"]);
		}
		else
		{
			$t = $c_ol->begin();
		}
	

		if (!$ol->count())
		{
			// if not, create
			$o = obj();	
			$o->set_class_id(CL_MRP_CASE);
			//$o->set_parent($co->prop("projects_folder"));
			$o->set_parent(1256);
			$o->set_prop("extern_id", $id);
			$o->set_prop("state", 1); // MRP_STATUS_NEW
			if ($t)
			{
				$o->set_prop("customer", $t->id());
			}
			$o->save();
			$this->_upd_proj_o($o, $dat);
			$o->save();

			if ($t)
			{
				$t->connect(array(
					"to" => $o->id(),
					"reltype" => "RELTYPE_CUSTOMER"
				));
			}

			$o->connect(array(
				"to" => aw_ini_get("prisma.ws"),
				"reltype" => "RELTYPE_MRP_OWNER"
			));
		}
		else
		{
			// if yes, update
			$o = $ol->begin();
			if ($t)
			{
				$o->set_prop("customer", $t->id());
			}
			$this->_upd_proj_o($o, $dat);

			if (!$o->is_connected_to(array("to" => aw_ini_get("prisma.ws"))))
			{
				$o->connect(array(
					"to" => aw_ini_get("prisma.ws"),
					"reltype" => "RELTYPE_MRP_OWNER"
				));
			}

			$o->save();
		}

		aw_restore_messages();

		return $o->id();
	}

	function _imp_new_cust($co, $id)
	{
		$db = $this->_get_conn();
		$dat = $db->db_fetch_row("SELECT * FROM kliendid WHERE KliendiID = '$id'");
		if (!$dat)
		{
			return false;
		}
		$o = obj();	
		$o->set_class_id(CL_CRM_COMPANY);
		$o->set_parent($co->id());
		$o->set_prop("extern_id", $id);
		$o->save();
		$this->_upd_cust_o($o, $dat);
		$o->save();
		return $o;
	}

	function get_prop_value(&$prop, $rpn)
	{
		switch($rpn)
		{
			case "makett":
			case "kromaliin":
			case "naidis":
				if ($prop["value"] == 1)
				{
					$prop["value"] = t("Jah");
				}
				else
				{
					$prop["value"] = t("Ei");
				}
				break;

			case "trykise_ehitus":
				if (!$prop["value"])
				{
					return PROP_IGNORE;
				}
				// read from their table. damn. 
				$c = $this->_get_conn();
				if (!$c)
				{
					return PROP_IGNORE;
				}
				$prop["value"] = $c->db_fetch_field("SELECT TrükiseEhitus as e FROM `trükise ehitus` WHERE EhitusID = '$prop[value]'", "e");
				return PROP_OK;
				break;

			default:
				if ($prop["value"] == "")
				{
					return PROP_IGNORE;
				}
		}
		return PROP_OK;
	}
}
?>
