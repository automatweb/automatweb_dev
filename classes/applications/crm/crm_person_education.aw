<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_education.aw,v 1.13 2008/04/07 19:11:13 instrumental Exp $
// crm_person_education.aw - Haridus 
/*

@classinfo syslog_type=ST_CRM_PERSON_EDUCATION no_name=1 no_comment=1 no_status=1
@tableinfo kliendibaas_haridus index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@default table=kliendibaas_haridus

@property school_1 type=relpicker reltype=RELTYPE_SCHOOL field=school_1
@caption Kool

@property school_2 type=textbox field=school_2
@caption Muu kool

@property degree type=select field=degree
@caption Akadeemiline kraad

@property field type=classificator reltype=RELTYPE_FIELD store=connect
@caption Valdkond

@property speciality type=textbox field=speciality
@caption Eriala

@property main_speciality type=chooser field=main_speciality
@caption P&otilde;hieriala

@property in_progress type=chooser field=in_progress
@caption Omandamisel

@property dnf type=checkbox ch_value=1 field=dnf
@caption Alustatud, kuid j&auml;&auml;nud l&otilde;petamata

@property obtain_language type=relpicker reltype=RELTYPE_LANGUAGE field=obtain_language
@caption Omandamise keel

# format=month,year doesn't work!!!! -kaarel
# @property start type=date_select field=start year_from=1950
# Since I only need it for years, I can just use select.
@property start type=select field=start
@caption Algus

# format=month,year doesn't work!!!! -kaarel
# @property end type=date_select field=end year_from=1950 
# Since I only need it for years, I can just use select.
@property end type=select field=end
@caption L&otilde;pp

@property end_date type=date_select field=end_date
@caption L&otilde;petamise kuup&auml;ev

@property diploma_nr type=textbox field=diploma_nr
@caption Diplomi number

@reltype FIELD value=1 clid=CL_META
@caption Valdkond

@reltype SCHOOL value=2 clid=CL_CRM_COMPANY
@caption Kool

@reltype LANGUAGE value=3 clid=CL_LANGUAGE
@caption Omandamise keel

*/

class crm_person_education extends class_base
{
	function crm_person_education()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_EDUCATION
		));
		$this->degree_options = array(
			"pohiharidus" => t("P&otilde;hiharidus"),
			"keskharidus" => t("Keskharidus"),
			"keskeriharidus" => t("Kesk-eriharidus"),
			"diplom" => t("Diplom"),
			"bakalaureus" => t("Bakalaureus"),
			"magister" => t("Magister"),
			"doktor" => t("Doktor"),
			"teadustekandidaat" => t("Teaduste kandidaat"),
		);
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			/*
			case "start":
			case "end":
				//$value = mktime(0, 0, 0, $prop["value"]["month"], 1, $prop["value"]["year"]);
				$value = mktime(0, 0, 0, 1, 1, $prop["value"]);
				$prop["value"] = $value;
				break;
			*/
		}
		return $retval;
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "end":
			case "start":
				$ops["---"] = "---";
				for($i = 1950; $i <= date("Y") + 5; $i++)
				{
					$ops[$i] = $i;
				}
				$prop["options"] = $ops;
				//$prop["value"] = date("Y", $prop["value"]);
				break;

			case "main_speciality":
				$arr["prop"]["options"] = array(
					1 => t("Jah"),
					0 => t("Ei"),
				);
				break;

			case "in_progress":
				$arr["prop"]["options"] = array(
					1 => t("Jah"),
					0 => t("Ei"),
				);
				break;

			case "degree":
				$arr["prop"]["options"] = $this->degree_options;
				break;
		};
		return $retval;
	}

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ($tbl == "kliendibaas_haridus" && $field == "")
		{
			$this->db_query("create table kliendibaas_haridus (oid int primary key)");
			return true;
		}

		$props = array(
			"main_speciality" => "main_speciality",
			"in_progress" => "in_progress",
			"obtain_language" => "obtain_language",
			"start" => "start",
			"end" => "end",
			"end_date" => "end_date",
			"school_2" => "school",
			"degree" => "degree",
			"speciality" => "speciality",
			"diploma_nr" => "diploma_nr",
		);

		switch($field)
		{
			case "school_1":
			case "main_speciality":
			case "in_progress":
			case "obtain_language":
			case "start":
			case "end":
			case "end_date":
			case "dnf":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "int"
				));
				$ol = new object_list(array(
					"class_id" => CL_CRM_PERSON_EDUCATION,
					"parent" => array(),
					"site_id" => array(),
					"lang_id" => array(),
					"status" => array(),
				));
				foreach($ol->arr() as $o)
				{
					$value = $o->meta($props[$field]);
					$oid = $o->id();
					$this->db_query("
						INSERT INTO
							kliendibaas_haridus (oid, $field)
						VALUES
							('$oid', '$value')
						ON DUPLICATE KEY UPDATE
							$field = '$value'
					");
				}
				return true;

			case "school_2":
			case "degree":
			case "speciality":
			case "diploma_nr":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "varchar(50)"
				));
				$ol = new object_list(array(
					"class_id" => CL_CRM_PERSON_EDUCATION,
					"parent" => array(),
					"site_id" => array(),
					"lang_id" => array(),
					"status" => array(),
				));
				foreach($ol->arr() as $o)
				{
					$value = $o->meta($props[$field]);
					$oid = $o->id();
					$this->db_query("
						INSERT INTO
							kliendibaas_haridus (oid, $field)
						VALUES
							('$oid', '$value')
						ON DUPLICATE KEY UPDATE
							$field = '$value'
					");
				}
				return true;
		}

		return false;
	}
};
?>
