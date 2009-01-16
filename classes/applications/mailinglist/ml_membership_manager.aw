<?php
/*
@classinfo syslog_type=ST_ML_MEMBERSHIP_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=instrumental
@tableinfo aw_ml_membership_manager master_index=brother_of master_table=objects index=aw_oid

@default table=aw_ml_membership_manager
@default group=general

@property lists type=relpicker reltype=RELTYPE_LIST multiple=1 store=connect
@caption Mailinglistid, mida tabelis kuvada

@property membership type=table store=no
@caption Liikmelisuse tabel

@reltype LIST value=1 clid=CL_ML_LIST
@caption Mailinglistid, mida tabelis kuvada

*/

class ml_membership_manager extends class_base
{
	function ml_membership_manager()
	{
		$this->init(array(
			"tpldir" => "applications/mailinglist/ml_membership_manager",
			"clid" => CL_ML_MEMBERSHIP_MANAGER
		));
	}

	function _init_membership(&$arr)
	{
		$t = &$arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "list",
			"caption" => t("Mailinglist"),
		));
		$t->define_field(array(
			"name" => "membership",
			"caption" => t("Olen liige"),
		));
	}

	public function _get_membership($arr)
	{
		$this->_init_membership($arr);
		$t = &$arr["prop"]["vcl_inst"];

		$inst = get_instance(CL_ML_MEMBER);

		$mail_id = obj(get_instance(CL_USER)->get_current_person())->prop("email");
		$mail = is_oid($mail_id) ? obj($mail_id)->mail : false;

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_LIST")) as $conn)
		{
			$t->define_data(array(
				"list" => $this->can("edit", $conn->prop("to")) && is_admin() ? html::obj_change_url($conn->prop("to")) : $conn->prop("to.name"),
				"membership" => html::checkbox(array(
					"name" => "membership[".$conn->prop("to")."]",
					"value" => 1,
					"checked" => $mail === false ? false : $inst->check_member(array(
						"email" => $mail,
						"folder" => obj($conn->prop("to"))->prop("def_user_folder"),
					)) !== false,
				)).html::hidden(array(
					"name" => "membership_old[".$conn->prop("to")."]",
					"value" => ($mail === false ? false : $inst->check_member(array(
						"email" => $mail,
						"folder" => obj($conn->prop("to"))->prop("def_user_folder"),
					)) !== false) ? 1 : 0,
				)),
			));
		}
	}

	public function _set_membership($arr)
	{
		$inst = get_instance(CL_ML_MEMBER);
		$mail_id = obj(get_instance(CL_USER)->get_current_person())->prop("email");
		if(is_oid($mail_id))
		{
			$mail = obj($mail_id)->mail;
			foreach(safe_array($arr["request"]["membership_old"]) as $k => $v)
			{
				if(isset($arr["request"]["membership"][$k]) && $v == 0)
				{
					$inst->subscribe_member_to_list(array(
						"email" => $mail,
						"list_id" => $k,
					));
				}
				elseif(!isset($arr["request"]["membership"][$k]) && $v == 1)
				{
					$inst->unsubscribe_member_from_list(array(
						"email" => $mail,
						"list_id" => $k,
					));
				}
			}
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_ml_membership_manager(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}

?>
