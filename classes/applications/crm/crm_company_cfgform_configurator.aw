<?php
/*
@classinfo syslog_type=ST_CRM_COMPANY_CFGFORM_CONFIGURATOR relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=instrumental
@tableinfo aw_crm_company_cfgform_configurator master_index=brother_of master_table=objects index=aw_oid

@default table=aw_crm_company_cfgform_configurator
@default group=general

@property org_cfgform type=relpicker reltype=RELTYPE_ORG_CFGFORM store=connect
@caption Organisatsiooni seadete vorm

@property cfgview_actions type=select field=cfgform_method
@caption Lisamine/muutmine

@property ca_change type=chooser field=aw_ca_change
@caption Millist organisatsiooni muutmisvaates kuvada?

@property ca_change_org type=relpicker reltype=RELTYPE_CHANGE_ORG store=connect
@caption Muutmisvaates kuvatav organisatsioon

@property org_parent type=relpicker reltype=RELTYPE_ORG_PARENT store=connect
@caption Kaust, kuhu organisatsioon salvestatakse

@property make_user type=checkbox field=aw_make_user
@caption Tee organisatsioonile kasutaja

@property mu_send_welcome_mail type=checkbox field=aw_mu_send_welcome_mail
@caption Saada kasutajale logimisandmetega e-kiri

@property mu_welcome_mail_subject type=textbox field=aw_mu_welcome_mail_subject
@caption Logimisandmetega e-kirja pealkiri

@property mu_welcome_mail_content type=textarea field=aw_mu_welcome_mail_content
@caption Logimisandmetega e-kirja sisu

@property mu_generate_username type=select field=aw_mu_generate_username
@caption Kasutajanime genereerimise meetod

@property subscribe_to_mailinglists type=checkbox field=aw_subscribe_to_mailinglists
@caption Lisa organisatsiooni e-post mailinglisti(desse)

@property stm_lists type=relpicker multiple=1 store=connect
@caption Meilinglistid, millega liituda

@property org_em_to_user type=checkbox field=aw_org_em_to_user
@caption Pane organisatsiooni meiliaadress ka sisseloginud kasutaja meiliaadressiks

@property org_em_to_person type=checkbox field=aw_org_em_to_person
@caption Pane organisatsiooni meiliaadress ka sisseloginud isiku meiliaadressiks

@property org_ph_to_person type=checkbox field=aw_org_em_to_person
@caption Pane organisatsiooni telefon ka sisseloginud isiku telefoniks

@property check_email type=checkbox field=aw_check_email default=1
@caption Kontrolli e-postiaadressi &otilde;igsust

###

@reltypes ORG_CFGFORM value=1 clid=CL_CFGFORM
@caption Organisatsiooni seadete vorm

@reltypes ORG_PARENT value=2 clid=CL_MENU
@caption Kaust, kuhu organisatsioon salvestatakse

@reltype CHANGE_ORG value=3 clid=CL_CRM_COMPANY
@caption Muutmisvaates kuvatav organisatsioon

*/

class crm_company_cfgform_configurator extends class_base
{
	function crm_company_cfgform_configurator()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_company_cfgform_configurator",
			"clid" => CL_CRM_COMPANY_CFGFORM_CONFIGURATOR
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_crm_company_cfgform_configurator(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_mu_generate_username":
			case "aw_ca_change":
			case "aw_subscribe_to_mailinglists":
			case "aw_mu_generate_username":
			case "aw_mu_send_welcome_mail":
			case "aw_make_user":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "mu_welcome_mail_subject":
			case "mu_welcome_mail_content":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "text"
				));
				return true;
		}

		return false;
	}
}

?>
