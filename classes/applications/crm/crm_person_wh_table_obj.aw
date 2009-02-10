<?php

class crm_person_wh_table_obj extends _int_object
{
	public function get_people_list()
	{
		$ol = new object_list($this->connections_from(array("type" => "RELTYPE_PERSON")));
		return $ol;
	}

	public function get_must_wh_list_for_person($person)
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_PERSON_REQUIRED_WH_ENTRY,
			"lang_id" => array(),
			"site_id" => array(),
			"person" => $person->id(),
			"wh_table" => $this->id()
		));
		return $ol;
	}

	public function add_must_wh_entry_for_person($person, $data)
	{
		$o = obj();
		$o->set_class_id(CL_CRM_PERSON_REQUIRED_WH_ENTRY);
		$o->set_parent($this->id());

		$o->person = $person->id();
		$o->wh_table = $this->id();

		$from = date_edit::get_timestamp($data["from"]);
		$to = date_edit::get_timestamp($data["to"]);

		$o->set_name(sprintf(t("Isiku %s n&otilde;utud t&ouml;&ouml;tunnid vahemikus %s - %s"), $person->name, date("d.m.Y", $from), date("d.m.Y", $to)));
		$o->from = $from; 
		$o->to = $to; 
		$o->hours_total = $data["total"];
		$o->hours_cust = $data["cust"];
		$o->hours_other = $data["other"];
		$o->save();
	}
}

?>
