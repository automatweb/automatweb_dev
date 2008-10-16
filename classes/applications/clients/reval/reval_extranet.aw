<?php
/*
@classinfo syslog_type=ST_REVAL_EXTRANET relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_reval_extranet master_index=brother_of master_table=objects index=aw_oid

@default table=aw_reval_extranet
@default group=general

*/

class reval_extranet extends class_base
{
	function reval_extranet()
	{
		$this->init(array(
			"tpldir" => "applications/clients/reval/reval_extranet",
			"clid" => CL_REVAL_EXTRANET
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

	private function _get_user_id()
	{
		return (int)$_SESSION["reval_extranet"]["id"];
	}

	function display_login()
	{
		$_SESSION["request_uri_before_auth"] = aw_global_get("REQUEST_URI");

		$this->read_template("need_login.tpl");
		lc_site_load("reval_extranet", $this);

		$this->vars(array(
			"reforb" => $this->mk_reforb("login", array("fail_return" => get_ru()), "users")
		));
		return $this->parse();
	}

	/**
		@attrib name=show_tab1 nologin="1"
	**/
	function show($arr)
	{
		if (!$this->_get_user_id())
		{
			return $this->display_login();
		}
		$this->read_template("show.tpl");
		lc_site_load("reval_extranet", $this);
		$this->_disp_company_profile_edit($arr["id"]);
		$this->_disp_acct_mgr($arr["id"]);
		$this->_disp_event_mgr($arr["id"]);
		$this->_insert_tabs($arr["id"]);
		return $this->parse();
	}

	/**
		@attrib name=show_tab2 nologin="1"
	**/
	function show_tab2($arr)
	{
		if (!$this->_get_user_id())
		{
			return $this->display_login();
		}
		$this->read_template("show_tab2.tpl");
		lc_site_load("reval_extranet", $this);
		$this->_insert_tabs($arr["id"]);
		return $this->parse();
	}

	private function _disp_acct_mgr($id)
	{
		$return = $this->do_orb_method_call(array(
			"action" => "GetCompanyAccountManagers",
			"class" => "http://markus.ee/RevalServices/Customers/",
			"params" => array(
				"companyId" => $_SESSION["reval_extranet"]["data"]["CompanyId"],
				"languageId" => $this->_get_web_language_id()
			),
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServicesTest/CustomerService.asmx"
		));

		if ($return["GetCompanyAccountManagersResult"]["ResultCode"] != "Success")
		{
			return $this->display_login();
		}
		$ctry_list = array();
		foreach($return["GetCompanyAccountManagersResult"]["AccountManagers"]["AccountManager"] as $mgr)
		{
			$ctry_list[$mgr["ID"]] = $mgr["CountryName"];
		}
		foreach($return["GetCompanyAccountManagersResult"]["AccountManagers"]["AccountManager"] as $mgr)
		{
			if (!$_GET["mgr_id"] || $mgr["ID"] == $_GET["mgr_id"])
			{
				$this->vars(array(
					"mgr_fn" => reval_customer::_f($mgr["FirstName"]),
					"mgr_ln" => reval_customer::_f($mgr["LastName"]),
					"mgr_email" => reval_customer::_f($mgr["Email"]),
					"mgr_phone" => "__undefined__",//reval_customer::_f($mgr[""]),
					"mgr_mobile" => "__undefined__",//reval_customer::_f($mgr["LastName"]),
					"mgr_skype" => reval_customer::_f($mgr["SkypeID"]),
				));
				break;
			}
		}
		$this->vars(array(
			"acct_mgr_ctry_select" => $this->picker("", $ctry_list)
		));
	}

	private function _disp_event_mgr($id)
	{
		$return = $this->do_orb_method_call(array(
			"action" => "GetCompanyEventManagers",
			"class" => "http://markus.ee/RevalServices/Customers/",
			"params" => array(
				"companyId" => $_SESSION["reval_extranet"]["data"]["CompanyId"],
				"languageId" => $this->_get_web_language_id()
			),
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServicesTest/CustomerService.asmx"
		));

		if ($return["GetCompanyEventManagersResult"]["ResultCode"] != "Success")
		{
			return $this->display_login();
		}

		$ctry_list = array();
		if (!is_array($return["GetCompanyEventManagersResult"]["EventManagers"]["EventManager"][0]))
		{
			$tmp = $return;
			$return["GetCompanyEventManagersResult"]["EventManagers"] = array();
			$return["GetCompanyEventManagersResult"]["EventManagers"]["EventManager"] = array(0 => $tmp["GetCompanyEventManagersResult"]["EventManagers"]["EventManager"]);
		}

		foreach($return["GetCompanyEventManagersResult"]["EventManagers"]["EventManager"] as $mgr)
		{
			$ctry_list[$mgr["ID"]] = $mgr["CountryName"];
		}
		foreach($return["GetCompanyEventManagersResult"]["EventManagers"]["EventManager"] as $mgr)
		{
			if (!$_GET["emgr_id"] || $mgr["ID"] == $_GET["emgr_id"])
			{
				$this->vars(array(
					"emgr_fn" => reval_customer::_f($mgr["FirstName"]),
					"emgr_ln" => reval_customer::_f($mgr["LastName"]),
					"emgr_email" => reval_customer::_f($mgr["Email"]),
					"emgr_phone" => "__undefined__",//reval_customer::_f($mgr[""]),
					"emgr_mobile" => "__undefined__",//reval_customer::_f($mgr["LastName"]),
					"emgr_skype" => reval_customer::_f($mgr["SkypeID"]),
				));
				break;
			}
		}
		$this->vars(array(
			"event_mgr_ctry_select" => $this->picker("", $ctry_list)
		));
	}

	private function _insert_tabs($id)
	{
		$this->vars(array(
			"tab1_url" => $this->mk_my_orb("show_tab1", array("id" => $id, "section" => aw_global_get("section"))),
			"tab2_url" => $this->mk_my_orb("show_tab2", array("id" => $id, "section" => aw_global_get("section"))),
		));
	}

	public static function get_company_id()
	{	
		return $_SESSION["reval_extranet"]["data"]["CompanyId"];
	}

	private function _disp_company_profile_edit($id)
	{
		$return = $this->do_orb_method_call(array(
			"action" => "GetCompanyProfile",
			"class" => "http://markus.ee/RevalServices/Customers/",
			"params" => array(
				"companyId" => self::get_company_id(),
				"languageId" => $this->_get_web_language_id()
			),
			"method" => "soap",
			"server" => "http://195.250.171.36/RevalServicesTest/CustomerService.asmx"
		));

		if ($return["GetCompanyProfileResult"]["ResultCode"] != "Success")
		{
			return $this->display_login();
		}

		$d = $return["GetCompanyProfileResult"]["Profile"];
		list($ct_fn, $ct_ln) = explode(" ", reval_customer::_ef($d["ContactName"]), 2);
		$this->vars(array(
			"company_name_local" => reval_customer::_f($d["CompanyName"]),
			"company_name_eng" => reval_customer::_f($d["CompanyNameInEnglish"]),
			"company_reg_no" => reval_customer::_f($d["CompanyRegisterNr"]),
			"company_contract_no" => "__undefined__",//reval_customer::_f($d[""]),
			"company_vat_no" => reval_customer::_f($d["CompanyVatNumber"]),
			"adr1" => reval_customer::_ef($d["CompanyBusinessAddressLine1"]),
			"adr2" => reval_customer::_ef($d["CompanyBusinessAddressLine2"]),
			"city" => reval_customer::_ef($d["CompanyBusinessCityName"]),
			"zip" => reval_customer::_ef($d["CompanyBusinessPostalCode"]),
			"ct_firstname" => $ct_fn,
			"ct_lastname" => $ct_ln,
			"ct_email" => reval_customer::_ef($d["ContactEmail"]),
			"ct_phone" => reval_customer::_ef($d["ContactPhone"]),
			"ct_phone" => reval_customer::_ef($d["ContactPhone"]),
			"ct_mobile" => reval_customer::_ef($d["ContactMobile"]),
			"ct_business_title" => reval_customer::_ef($d["ContactBusinessTitle"]),
			"reforb" => $this->mk_reforb("submit_view1", array("id" => $id, "ru" => get_ru()))
		));
	}

	private function _get_web_language_id()
	{
		$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
		return get_instance(CL_OWS_BRON)->get_web_language_id($lc);
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_reval_extranet(aw_oid int primary key)");
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

	/**
		@attrib name=submit_view1 nologin=1
	**/
	public function submit_view1($arr)
	{
		// validate
		// service
		die("service not implemented yet");
		return $arr["ru"];
	}
}

?>
