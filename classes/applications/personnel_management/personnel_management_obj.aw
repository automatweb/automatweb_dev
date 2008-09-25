<?php

class personnel_management_obj extends _int_object
{
	function prop($k)
	{
		if($k == "perpage" && !is_numeric(parent::prop($k)))
		{
			return 20;
		}
		return parent::prop($k);
	}

	/** Sends CV to e-mail.
	@attrib name=notify_of_new_cv api=1 params=name

	@param person_obj required type=object acl=view

	@param to required type=string acl=view

	@param pm_obj optional type=object acl=view

	**/
	function notify_of_new_cv($arr)
	{
		// $person_oid, $to, $pm_obj
		extract($arr);
		$content = get_instance(CL_CRM_PERSON)->show_cv(array(
			"id" => $person_obj->id(),
			"cv" => "cv/".basename($pm_obj->prop("cv_tpl")),
		));
		$msg = obj();
		$msg->set_class_id(CL_MESSAGE);
		$msg->set_parent($person_obj->id());
		$msg->name = t("Uus CV on lisatud");
		$msg->html_mail = 1;
		$msg->mto = $to;
		$msg->message = $content;
		$msg->save();
		get_instance(CL_MESSAGE)->send_message(array(
			"id" => $msg->id(),
		));
		$msg->delete();
	}

	function on_add_person($arr)
	{
		$this->send_naughtyfication_mail_if_necessary($arr);
	}

	// Checks if I have to send notification mail.
	private function send_naughtyfication_mail_if_necessary($arr)
	{
		$o = obj($arr["oid"]);
		$pm = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault());
		if($pm->persons_fld == $o->parent() && strlen(trim($pm->notify_mail)) > 0)
		{
			$this->notify_of_new_cv(array(
				"person_obj" => $o,
				"to" => $pm->notify_mail,
				"pm_obj" => $pm,
			));
		}
	}
}

?>
