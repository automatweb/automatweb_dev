<?php
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_CRM_PERSON, on_save_person)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_CRM_COMPANY, on_save_co)

*/

class crm_user_creator extends core
{
	function crm_user_creator()
	{
		$this->init();
	}

	function on_save_person($arr)
	{
		$person = obj($arr["oid"]);
		if ($person->meta("no_create_user_yet") == true)
		{
			return;
		}

		// check where the user is employed
		$co = $this->get_co_for_person($arr["oid"]);

		// if the company has the create users flag
		if ($co && $co->prop("do_create_users"))
		{
			// find all proffessions and units
			// for each check if it's parents already has a group
			// if not, create them
			$this->check_co_groups($co);

			// add user to all necessary groups
			$this->check_person_groups($co, obj($arr["oid"]));
		}
	}

	function on_save_co($arr)
	{
		$co = obj($arr["oid"]);

		// if the company has the create users flag
		if ($co && $co->prop("do_create_users"))
		{
			// find all proffessions and units
			// for each check if it's parents already has a group
			// if not, create them
			$this->check_co_groups($co);
		}
	}

	function get_co_for_person($p_id)
	{
		$p = obj($p_id);
		$wc = $p->prop("work_contact");
		if (is_oid($wc) && $this->can("view", $wc))
		{
			return obj($wc);
		}
		return false;
	}

	function check_co_groups($co)
	{
		// find all proffessions and units
		// for each check if it's parents already has a group
		// if not, create them
		
		// relations are from company, to crm_section/crm_proffession
		// then from section to subsection/proffession
		// so, start from company and go from there
		
		// get company group
		$co_grp = $this->_check_company_group($co);

		// get proffessions and categories from co and recurse
		$ol = new object_list(
			$co->connections_from(array(
				"type" => array(
					28, // CRM_COMPANY.RELTYPE_SECTION
					29  // CRM_COMPANY.RELTYPE_PROFFESSIONS
				)
			))
		);
		$this->_req_check_grps($co_grp, $ol);
	}

	function _check_company_group($co)
	{
		// info on group creation is stored in company as relation
		obj_set_opt("no_cache", 1);
		$grp = $co->get_first_obj_by_reltype(36);
		obj_set_opt("no_cache", 0);
		if (!$grp)
		{
			// create group under group folder
			$grp = obj();
			$grp->set_parent(aw_ini_get("groups.tree_root"));
			$grp->set_class_id(CL_GROUP);
			$grp->set_name($co->name());
			$grp->set_prop("priority", 100);
			$grp->set_prop("type", GRP_REGULAR);
			$grp->save();
			// connect co to grp
			$co->connect(array(
				"to" => $grp->id(),
				"reltype" => "RELTYPE_GROUP"
			));
		}
		else
		{
			$grp->set_name($co->name());
			$grp->save();
		}
		
		return $grp;
	}

	function _req_check_grps($parent, $ol, $force_has = false, $force_has_prof = false)
	{
		foreach($ol->arr() as $o)
		{
			if ($o->class_id() == CL_CRM_SECTION && !$o->prop("has_group") && !$force_has)
			{
				continue;
			}

			if ($o->class_id() == CL_CRM_PROFFESSION && !$force_has_prof)
			{
				continue;
			}

			if ($o->prop("has_group_subs"))
			{
				$force_has = true;
			}

			if ($o->prop("has_group_subs_prof"))
			{
				$force_has_prof = true;
			}

			// check of the object has a group relation
			obj_set_opt("no_cache", 1);
			$grp = $o->get_first_obj_by_reltype("RELTYPE_GROUP");
			obj_set_opt("no_cache", 0);
			if (!$grp)
			{
				// if not, create the group
				$grp = obj();
				$grp->set_parent($parent->id());
				$grp->set_class_id(CL_GROUP);
				$grp->set_name($o->name());
				$grp->set_prop("priority", $parent->prop("priority", 100));
				$grp->set_prop("type", GRP_REGULAR);
				$grp->save();
				// connect co to grp
				$o->connect(array(
					"to" => $grp->id(),
					"reltype" => "RELTYPE_GROUP"
				));
				
			}
			else
			{
				$grp->set_name($o->name());
				$grp->save();
			}

			if ($o->class_id() == CL_CRM_SECTION)
			{
				// get list of subsections and proffessions
				$ol_s = new object_list(
					$o->connections_from(array(
						"type" => array(
							1, // RELTYPE_SECTION
							3  // RELTYPE_PROFFESSION
						)
					))
				);
				// recurse
				$this->_req_check_grps($grp, $ol_s, $force_has, $force_has_prof);
			}
		}
	}

	function check_person_groups($co, $pers)
	{
		// check if the user exists
		// if not, create user
		$user = $this->_check_person_user($pers);

		// find all proffessions and units and add to those, if not present
		$this->_check_person_groups($pers, $user);
	}

	function _check_person_user($pers)
	{
		$cp = get_instance(CL_CRM_PERSON);

		if (($us = $cp->has_user($pers)))
		{
			return $us;
		}
		else
		{
			// create user
			$us = get_instance(CL_USER);

			$o_uid = $uid = $pers->prop("firstname").".".$pers->prop("lastname");

			while ($us->username_is_taken($uid))
			{
				$uid = $o_uid.".".sprintf("%03d", ++$cnt);
			}

			$pwd = isset($_POST["password"]) ? $_POST["password"] : $pers->prop("password");
			$uo = $us->add_user(array(
				"uid" => $uid,
				"password" => $pwd
			));

			$uo->connect(array(
				"to" => $pers->id(),
				"reltype" => 2 // CL_USER.RELTYPE_PERSON
			));
			return $uo;
		}
	}

	function _check_person_groups($pers, $user)
	{
		$g = get_instance(CL_GROUP);

		// get all sections and proffessions (RELTYPE_RANK), find their groups and add user to group
		foreach($pers->connections_from(array("type" => array(7 /* RANK */, 21 /* SECTION */))) as $c)
		{
			$o = $c->to();
			// get grp
	
			$grp = $o->get_first_obj_by_reltype("RELTYPE_GROUP");
			if ($grp)
			{
				$g->add_user_to_group($user, $grp);
			}
		}
	}
}
?>
