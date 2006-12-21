<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp_manager.aw,v 1.2 2006/12/21 14:12:12 tarvo Exp $
// rfp_manager.aw - RFP Haldus 
/*

@classinfo syslog_type=ST_RFP_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@groupinfo rfps caption="Pakkumise saamis palved" submit=no
	@property rfps type=table group=rfps no_caption=1

*/

class rfp_manager extends class_base
{
	function rfp_manager()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/rfp_manager",
			"clid" => CL_RFP_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
			case "rfps":
				$t = &$prop["vcl_inst"];
				$t->define_field(array(
					"name" => "function",
					"caption" => t("&Uuml;ritus"),
				));
				$t->define_field(array(
					"name" => "org",
					"caption" => t("Organisatsioon"),
				));
				$t->define_field(array(
					"name" => "response_date",
					"caption" => t("Tagasiside aeg"),
				));
				$t->define_field(array(
					"name" => "arrival_date",
					"caption" => t("Saabumisaeg"),
				));
				$t->define_field(array(
					"name" => "departure_date",
					"caption" => t("Lahkumisaeg"),
				));
				$t->define_field(array(
					"name" => "acc_need",
					"caption" => t("Maujutusvajadus"),
				));
				$t->define_field(array(
					"name" => "delegates",
					"caption" => t("Inimeste arv"),
				));
				$t->define_field(array(
					"name" => "contact_pers",
					"caption" => t("Kontaktisik"),
				));
				$t->define_field(array(
					"name" => "contacts",
					"caption" => t("Kontaktandmed"),
				));


				foreach($this->get_rfps() as $oid => $obj)
				{
					$sres = aw_unserialize($obj->prop("search_result"));
					unset($places);
					foreach($sres as $res)
					{
						$places[] = $res["location"];
					}
					$c = array("billing_phone_number", "billing_email");
					unset($contacts);
					foreach($c as $e)
					{
						if(strlen(($cnt = $obj->prop($e))))
						{
							$contacts[] = $cnt;
						}
					}
					$t->define_data(array(
						"function" => html::href(array(
							"caption" => $obj->prop("function_name"),
							"url" => $this->mk_my_orb("change", array(
								"id" => $oid,
								"return_url" => get_ru(),
							),CL_RFP),
						)),
						"org" => $obj->prop("organisation"),
						"responose_date" => $obj->prop("response_date"),
						"arrival_date" => $obj->prop("arrival_date"),
						"departure_date" => $obj->prop("departure_date"),
						"acc_need" => ($obj->prop("accomondation_requirements") == 1)?t("Jah"):t("Ei"),
						"delegates" => $obj->prop("delegates_no"),
						"contact_pers" => "",
						"contacts" => join(", ", $contacts),
					));
				}
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
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//

	function get_rfps()
	{
		$o = new object_list(array(
			"class_id" => CL_RFP,
		));
		return $o->arr();
	}
}
?>
