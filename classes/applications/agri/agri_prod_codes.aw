<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/agri/Attic/agri_prod_codes.aw,v 1.1 2004/03/28 21:45:14 kristo Exp $
// agri_prod_codes.aw - Agri Tootekoodid 
/*

@classinfo syslog_type=ST_AGRI_PROD_CODES relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

@property code_parent type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Koodide kataloog

@groupinfo codes caption="Tootekoodid"

@property codes type=table group=codes 
@caption Tootekoodid

@groupinfo import caption="Impordi"

@property import type=fileupload group=import store=no
@caption Importfail

@property import_desc type=text group=import
@caption Importfaili kirjeldus

@reltype FOLDER value=1 clid=CL_MENU
@caption koodide kataloog

*/

class agri_prod_codes extends class_base
{
	function agri_prod_codes()
	{
		$this->init(array(
			"tpldir" => "applications/agri/agri_prod_codes",
			"clid" => CL_AGRI_PROD_CODES
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "codes":
				$this->do_codes_tbl($arr);
				break;

			case "import_desc":
				$prop["value"] = "Importfailis peab olema iga rea peal 3 komadega eraldatud v&auml;lja - tootekood, nimi, 1 v6i 0, vastavalt kas toote kohta on vaja sisestada andmed perioodiliselt v&otilde;i ei. <br><br>N&auml;ide:<br><br>123456789012,Suhkur,1<Br>123456789013,Jahu,0";
				break;
		};
		return $retval;
	}

	function init_codes_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "code",
			"caption" => "Kood",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "period",
			"caption" => "Eraldi perioodid",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "select",
			"caption" => "Kustuta",
			"align" => "center"
		));
	}

	function do_codes_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->init_codes_tbl($t);

		$ol = new object_list(array(
			"class_id" => CL_AGRI_PROD_CODE
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$row = array(
				"name" => $o->prop("name"),
				"code" => $o->prop("code")
			);
			$row["period"] = html::checkbox(array(
				"name" => "periods[".$o->id()."]",
				"value" => 1,
				"checked" => ($o->prop("period") == 1)
			));
			$row["select"] = html::checkbox(array(
				"name" => "select[".$o->id()."]",
				"value" => 1,
			));
			$t->define_data($row);
		}

		$t->define_data(array(
			"code" => html::textbox(array(
				"name" => "new[code]",
			)),
			"name" => html::textbox(array(
				"name" => "new[name]",
			)),
			"period" => html::checkbox(array(
				"name" => "new[period]",
				"value" => 1,
			))
		));

		$t->set_default_sortby("code");
		$t->set_default_sorder("desc");
		$t->sort_by();
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "codes":
				$this->do_save_codes_tbl($arr);
				break;

			case "import":
				if (is_uploaded_file($_FILES["import"]["tmp_name"]))
				{
					$fc = file($_FILES["import"]["tmp_name"]);
					foreach($fc as $line)
					{
						list($code,$name,$period) = explode(",", $line);
						if (!empty($code) && !empty($name))
						{
							// check if it exists
							$ol = new object_list(array(
								"class_id" => CL_AGRI_PROD_CODE,
								"code" => $code,
							));
							if ($ol->count() > 0)
							{
								$o = $ol->begin();
								$o->set_name($name);
								$o->set_prop("code", $code);
								$o->set_prop("period", $period);
								$o->save();
							}
							else
							{
								$o = obj();
								$o->set_class_id(CL_AGRI_PROD_CODE);
								$o->set_parent($arr["obj_inst"]->prop("code_parent"));
								$o->set_name($name);
								$o->set_prop("code", $code);
								$o->set_prop("period", $period);
								$o->save();
							}
						}
					}
				}
				break;
		}
		return $retval;
	}	

	function do_save_codes_tbl(&$arr)
	{
		$awa = new aw_array($arr["request"]["select"]);
		if (count($awa->get()) > 0)
		{
			$ol = new object_list(array(
				"class_id" => CL_AGRI_PROD_CODE,
				"oid" => array_keys($awa->get())
			));
			$ol->delete();
		}

		$ol = new object_list(array(
			"class_id" => CL_AGRI_PROD_CODE,
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->prop("period") != $arr["request"]["periods"][$o->id()])
			{
				$o->set_prop("period", $arr["request"]["periods"][$o->id()]);
				$o->save();
			}
		}

		if (!empty($arr["request"]["new"]["code"]) && !empty($arr["request"]["new"]["name"]))
		{
			$ol = new object_list(array(
				"class_id" => CL_AGRI_PROD_CODE,
				"code" => $arr["request"]["new"]["code"],
			));
			if ($ol->count() > 0)
			{
				$o = $ol->begin();
				$o->set_parent($arr["obj_inst"]->prop("code_parent"));
				$o->set_name($arr["request"]["new"]["name"]);
				$o->set_prop("code", $arr["request"]["new"]["code"]);
				$o->set_prop("period", $arr["request"]["new"]["period"]);
				$o->save();
			}
			else
			{
				$o = obj();
				$o->set_class_id(CL_AGRI_PROD_CODE);
				$o->set_parent($arr["obj_inst"]->prop("code_parent"));
				$o->set_name($arr["request"]["new"]["name"]);
				$o->set_prop("code", $arr["request"]["new"]["code"]);
				$o->set_prop("period", $arr["request"]["new"]["period"]);
				$o->save();
			}
		}
	}
}
?>
